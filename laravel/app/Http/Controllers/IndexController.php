<?php

namespace App\Http\Controllers;

use App\Models\LockerOrder;
use App\Models\ProcessQueue;
use App\Models\RaspberryDevice;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Hexters\CoinPayment\CoinPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
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
            abort(401);
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

        return view('locker.order.started', ['device' => $RaspberryDevice, 'order_id' => $LockerOrder->id, 'qr' => $qrcode, 'url' => $unlock_link]);
    }

    public function show_open_locker_page()
    {
        $signed_payment_link = URL::temporarySignedRoute('pay', now()->addMinutes(5), []);

        //URL::signedRoute('unlock', ['order_id' => $LockerOrder->id]);
        return view('locker.order.open', ['signed_payment_link' => $signed_payment_link]);
    }

    public function request_payment()
    {
        $order_id        = request()->input('opening_code');
        $hours_billabled = 0;

        $LockerOrder = LockerOrder::whereRaw("md5(id) =  '{$order_id}'")->first();
        $time_used   = $LockerOrder->current_duration;
        if ($time_used->hours == 0) {
            $hours_billabled = 1;
        } elseif ($time_used->minutes != 0) {
            $hours_billabled = $time_used->hours + 1;
        }
//dd($hours_billabled , env('HOUR_RATE'));
        $amount = $hours_billabled * env('HOUR_RATE');

        $transaction['order_id']     = md5($LockerOrder->id);        // invoice number
        $transaction['amountTotal']  = (FLOAT) $amount;
        $transaction['note']         = 'Locker Use';
        $transaction['buyer_name']   = 'self';
        $transaction['buyer_email']  = 'buyer@pagacripto.com';
        $transaction['redirect_url'] = url('/back_to_tarnsaction');  // When Transaction was comleted
        $transaction['cancel_url']   = url('/back_to_tarnsaction');  // When user click cancel link
        $transaction['items'][]      = [
            'itemDescription'    => 'Time',
            'itemPrice'          => (FLOAT) $amount,                 // USD
            'itemQty'            => (INT) 1,
            'itemSubtotalAmount' => (FLOAT) $amount                  // USD
        ];

        $payment_url = CoinPayment::generatelink($transaction);

        //dd($time_used,$LockerOrder);
        return view('locker.order.pay', ['Order' => $LockerOrder, 'time_billabled' => $hours_billabled, 'payment_url' => $payment_url]);
    }

    private function unlock_locker_data($order_id)
    {
        $LockerOrder = LockerOrder::whereRaw("id =  '{$order_id}'")->first();
        if ($LockerOrder->closening_paid_at != null) {
            abort(404);

            return;
        }
        $LockerOrder->closening_paid_at = now();
        $LockerOrder->save();
        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $LockerOrder->raspberry_device_id,
            'gpio_port'           => $LockerOrder->gpio_port,
            'command'             => 'available',
            'executed'            => 0
        ]);
    }

    public function unlock_order($order_id)
    {
        $this->unlock_locker_data($order_id);

        return response()->view('locker.order.closed')->header('Refresh', '5;url=/');
    }
}
