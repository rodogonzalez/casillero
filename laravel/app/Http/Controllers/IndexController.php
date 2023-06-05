<?php

namespace App\Http\Controllers;

use App\Helpers\BlockBee;
use App\Models\LockerOrder;
use App\Models\LockerOrderPayment;
use App\Models\ProcessQueue;
use App\Models\RaspberryDevice;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Hexters\CoinPayment\CoinPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Note;
use Order;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;

class IndexController extends Controller
{
    //
    public function request_locker($device_id)
    {
        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        $locker_ports    = [
            '4'  => '1',
            '9'  => '2',
            '10' => '3',
            '11' => '4',
            '13' => '5',
            '16' => '6',
            '20' => '7',
            '21' => '8',
            '23' => '9',
            '24' => '10',
            '26' => '11',
            '27' => '12'
        ];

        $ports_availables = [];

        foreach ($locker_ports as $locker_port => $label) {
            $locker_current_status = $RaspberryDevice->{"gpio_{$locker_port}_status"};
            $unlock_link           = URL::signedRoute('start', ['device_id' => $device_id, 'locker_id' => $locker_port]);
            $ports_availables[]    = [$locker_port => ['caption' => $label, 'status' => $locker_current_status, 'link' => $unlock_link]];
        }

        return view('device_user_view', ['device' => $RaspberryDevice, 'lockers' => $ports_availables]);

        //dd($RaspberryDevice);
        /*
            -. exploore which lockers are available oon the device
            -. assign randomly any and redirect to the payment page submitting the locker number to woo
            -. if payment successs then come back and open the locker,

        */
    }

    public function reset_device_feed($device_id)
    {
        Cache::forget('device_' . $device_id);
    }

    public function get_device_feed($device_id)
    {
        //return json_encode);
        $commands = Cache::rememberForever('device_' . $device_id, function () {
            return \App\Models\ProcessQueue::where('executed', 0)->where('raspberry_device_id', env('RASPBERRY_DEVICE_ID'))->get()->toArray();;
        });

        return response()
            ->json($commands);
    }

    public function request_open_locker()
    {
        $order_id    = request()->input('opening_code');
        $LockerOrder = LockerOrder::whereRaw("md5(id) =  '{$order_id}'")->first();
        $order_id    = $LockerOrder->id;
        $unlock_link = URL::signedRoute('unlock', ['order_id' => $LockerOrder->id]);

        return redirect($unlock_link);
    }

    public function start_order($device_id, $port)
    {
        if (!request()->hasValidSignature()) {
            // abort(401);
        }

        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        $LockerOrder     = LockerOrder::create([
            'raspberry_device_id' => $device_id,
            'gpio_port'           => $port,
            'woo_order_open'      => 0,
            'opening_paid_at'     => now(),
        ]);
        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $device_id,
            'locker_orders_id'    => $LockerOrder->id,
            'gpio_port'           => $port,
            'command'             => 'in-use',
            'executed'            => 0
        ]);
        $options = new QROptions(
            [
                'eccLevel'   => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'version'    => 5,
            ]
        );
        //$unlock_link = env('APP_URL') . ;
        $unlock_link = URL::signedRoute('unlock', ['order_id' => $LockerOrder->id]);
        $qrcode      = (new QRCode($options))->render(md5($LockerOrder->id));

        $locker_ports = [
            '4'  => '1',
            '9'  => '2',
            '10' => '3',
            '11' => '4',
            '13' => '5',
            '16' => '6',
            '20' => '7',
            '21' => '8',
            '23' => '9',
            '24' => '10',
            '26' => '11',
            '27' => '12'
        ];

        return view('locker.order.started', ['Order' => $LockerOrder, 'device' => $RaspberryDevice, 'locker_number' => $locker_ports[$LockerOrder->gpio_port], 'order_id' => $LockerOrder->id, 'qr' => $qrcode, 'url' => $unlock_link]);
    }

    public function show_open_locker_page()
    {
        //URL::signedRoute('unlock', ['order_id' => $LockerOrder->id]);
        return view('locker.order.open', ['signed_payment_link' => route('pay')]);
    }

    public function request_payment()
    {
        $order_id        = request()->input('opening_code');
        $hours_billabled = 0;

        $LockerOrder = LockerOrder::whereRaw("md5(id) =  '{$order_id}'")->first();
        $time_used   = $LockerOrder->duration;
        if ($time_used->hours == 0) {
            $hours_billabled = 1;
        } elseif ($time_used->minutes != 0) {
            $hours_billabled = $time_used->hours + 1;
        }
        //dd($hours_billabled , env('HOUR_RATE'));
        $amount = $hours_billabled * env('HOUR_RATE');

        $data = [
            'payment_method'       => 'cryptowoo',
            'payment_method_title' => 'Crypto',
            'set_paid'             => false,
            'billing'              => [
                'first_name' => 'Unknown',
                'last_name'  => 'Unknown',
                'address_1'  => '969 Market',
                'address_2'  => '',
                'city'       => 'San Francisco',
                'state'      => 'CA',
                'postcode'   => '94103',
                'country'    => 'US',
                'email'      => 'rodogonzalez@msn.com',
            ],
            'line_items' => [
                [
                    'product_id' => env('WOOCOMMERCE_PRODUCT_ID'),
                    'quantity'   => $hours_billabled,
                ]
            ],
        ];

        $coin         = env('BLOCKBEE_COIN');
        $my_address   = env('BLOCKBEE_WALLET_ADDRES');
        $callback_url = route('blockbee_callback', ['order_id' => $LockerOrder->id]);

        $parameters = ['order' => $LockerOrder->id, 'amount' => $amount];
        $size       = 400;

        $conversion = BlockBee::get_convert($coin, $amount, env('COINPAYMENT_CURRENCY'), env('BLOCKBEE_API'));

        $blockbee_params = [
            // ''=>
        ];
        $bb              = new BlockBee($coin, $my_address, $callback_url, $parameters, $blockbee_params, env('BLOCKBEE_API'));
        $payment_address = $bb->get_address();
        $options         = new QROptions(
            [
                'eccLevel'   => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'version'    => 5,
            ]
        );

        $qrcode = (new QRCode($options))->render($payment_address);

        $qrcode_with_amount = (new QRCode($options))->render("{$payment_address}?amount={$conversion->value_coin}");

        //$qrcode          = $bb->get_qrcode($conversion, $size);
        //dd($conversion,$qrcode);
        $LockerOrder->crypto_wallet_total_amount = $conversion->value_coin;
        $LockerOrder->crypto_wallet_address      = $payment_address;

        $LockerOrder->save();

        $unlock_link = URL::temporarySignedRoute('unlock-woo', now()->addMinutes(15), ['order_id' => $LockerOrder->id]);

        $locker_ports = [
            '4'  => '1',
            '9'  => '2',
            '10' => '3',
            '11' => '4',
            '13' => '5',
            '16' => '6',
            '20' => '7',
            '21' => '8',
            '23' => '9',
            '24' => '10',
            '26' => '11',
            '27' => '12'
        ];

        return view('locker.order.pay', ['Order' => $LockerOrder, 'locker_number' => $locker_ports[$LockerOrder->gpio_port], 'time_billabled' => $hours_billabled, 'unlock_link' => $unlock_link, 'wallet_addr' => $payment_address, 'qr' => $qrcode_with_amount, 'amount' => $conversion, 'fiat_amount' => $amount, 'callback' => $callback_url]);

        $data = [
            'note' => "Locker Order : {$LockerOrder->id}"
        ];

        // AFTER THIS IS THE REDIRECT TO WOOCOMMERCE PAY ORDER
        //$note = Order::createNote($order['id'], $data);

        //$payment_url = $order['payment_url'];
        return redirect($payment_url);

        //dd($time_used,$LockerOrder);
        //
    }

    public function payment_status($order_id)
    {
        $LockerOrder = LockerOrder::whereRaw("id =  '{$order_id}'")->first();
        $total_paid  = $LockerOrder->total_paid + (env('BEE_FEE') * $LockerOrder->total_paid);
        $response    = [];
        if ($total_paid >= $LockerOrder->crypto_wallet_total_amount && !is_null($LockerOrder->crypto_wallet_total_amount)) {
            $response = ['paid' => true, 'amount_confirmed' => $total_paid, 'amount_required' => $LockerOrder->crypto_wallet_total_amount, 'payments' => $LockerOrder->payments];
            if (is_null($LockerOrder->closening_paid_at)) {
                $LockerOrder->closening_paid_at = now();
                $LockerOrder->save();
            }

            //dd($LockerOrder);
            return json_encode($response);
        }

        $response = ['paid' => false, 'amount_confirmed' => $total_paid, 'amount_required' => $LockerOrder->crypto_wallet_total_amount, 'payments' => $LockerOrder->payments];

        return json_encode($response);

        //dd($total_paid);
    }

    public function blockbee_callback($order_id)
    {
        $LockerOrder     = LockerOrder::where('id', $order_id)->first();
        $payment_data    = BlockBee::process_callback($_GET);
        //if ($payment_data['confirmations']>3) return json_encode(['status'=>'ok']);
        $new_payment_log = LockerOrderPayment::create(['locker_orders_id' => $order_id, 'transaction_id' => $payment_data['txid_in'], 'amount_received' => $payment_data['value_forwarded_coin'], 'payment_details' => json_encode($payment_data)]);

        Log::info('Order ' . $order_id . ' got payment ' . $new_payment_log->id . ',' . print_r($payment_data, true));

        $total_paid = $LockerOrder->total_paid + (env('BEE_FEE') * $LockerOrder->total_paid);

        if ($total_paid >= $LockerOrder->crypto_wallet_total_amount && !is_null($LockerOrder->crypto_wallet_total_amount)) {
            $LockerOrder->closening_paid_at = now();
            $LockerOrder->save();
        }
    }

    private function unlock_locker_data($order_id)
    {
        $LockerOrder = LockerOrder::whereRaw("id =  '{$order_id}'")->first();
        if (!is_null($LockerOrder->closening_paid_at)) {
            //    abort(404);

            //  return;
        }
        $LockerOrder->closening_paid_at = now();
        $LockerOrder->save();
        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $LockerOrder->raspberry_device_id,
            'locker_orders_id'    => $LockerOrder->id,
            'gpio_port'           => $LockerOrder->gpio_port,
            'command'             => 'available',
            'executed'            => 0,
            'locker_orders_id'    => $LockerOrder->id,
        ]);
    }

    public function unlock_paid_order($order_id)
    {
        // check signature valid just for 10 minutes
        if (!request()->hasValidSignature()) {
            abort(401);
        }

        //$LockerOrder = LockerOrder::whereRaw("md5(woo_order_id) =  '". md5($order_id) . "'")->first();
        $LockerOrder = LockerOrder::whereRaw("id =  '{$order_id}'")->first();
        //dd($LockerOrder);
        if (is_null($LockerOrder->closening_paid_at)) {
            abort(401);

            //
            $LockerOrder->closening_paid_at = now();
            $LockerOrder->woo_order_closed  = now();
            $LockerOrder->save();
        }

        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $LockerOrder->raspberry_device_id,
            'locker_orders_id'    => $LockerOrder->id,
            'gpio_port'           => $LockerOrder->gpio_port,
            'command'             => 'available',
            'locker_orders_id'    => $LockerOrder->id,
            'executed'            => 0
        ]);

        return response()->view('locker.order.closed')->header('Refresh', '5;url=/');

        //ret
        $response = ['queue_event' => $ProcessQueue->id, 'status' => 'ok', 'details' => $LockerOrder->toArray()];

        return response()
            ->json($response);
    }

    public function unlock_order($order_id)
    {
        $this->unlock_locker_data($order_id);

        return response()->view('locker.order.closed')->header('Refresh', '5;url=/');
    }
}
