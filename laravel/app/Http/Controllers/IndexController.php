<?php

namespace App\Http\Controllers;

use App\Models\LockerOrder;
use App\Models\ProcessQueue;
use App\Models\RaspberryDevice;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Order;
use Illuminate\Support\Facades\Cache;


class IndexController extends Controller
{
    //
    public function request_locker($device_id)
    {
        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        $locker_ports    = [
            // the key will be stored in the db, the value will be shown as label;
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

            //if (in_array($locker_current_status, ['available', '', 'Available', null, 0])) {
            $ports_availables[] = [$locker_port => ['caption' => $label, 'status' => $locker_current_status]];
            //}
        }

        return view('device_user_view', ['device' => $RaspberryDevice, 'lockers' => $ports_availables]);

        //dd($RaspberryDevice);
        /*
            -. exploore which lockers are available oon the device
            -. assign randomly any and redirect to the payment page submitting the locker number to woo
            -. if payment successs then come back and open the locker,

        */
    }

    public function reset_device_feed($device_id){
        Cache::forget('device_' . $device_id);
    }

    public function get_device_feed($device_id){

            //return json_encode);
        


            $commands = Cache::rememberForever('device_' . $device_id, function () {
                return  \App\Models\ProcessQueue::where('executed', 0)->where('raspberry_device_id', env('RASPBERRY_DEVICE_ID'))->get()->toArray();
                ;
            });
            
            return response()
            ->json($commands);
    }

    public function start_order($device_id, $port)
    {
        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();

        /*
        $data = [
            'payment_method'       => 'bacs',
            'payment_method_title' => 'Direct Bank Transfer',
            'set_paid'             => true,
            'billing'              => [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'address_1'  => '969 Market',
                'address_2'  => '',
                'city'       => 'San Francisco',
                'state'      => 'CA',
                'postcode'   => '94103',
                'country'    => 'US',
                'email'      => 'john.doe@example.com',
                'phone'      => '(555) 555-5555',
            ],
            'shipping'             => [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'address_1'  => '969 Market',
                'address_2'  => '',
                'city'       => 'San Francisco',
                'state'      => 'CA',
                'postcode'   => '94103',
                'country'    => 'US',
            ],
            'line_items'           => [
                [
                    'product_id' => 40,
                    'quantity'   => 2,
                ],
                [
                    'product_id'   => 127,
                    'variation_id' => 23,
                    'quantity'     => 1,
                ],
            ],
        ];





*/

        $data = [
            'payment_method'       => '´bacs',
            'payment_method_title' => 'Direct Bank Transfer',
            'set_paid'             => true,
            //'payment_method_title' => 'Pay with crypto',
            /*
            'billing'              => [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'address_1'  => '969 Market',
                'address_2'  => '',
                'city'       => 'San Francisco',
                'state'      => 'CA',
                'postcode'   => '94103',
                'country'    => 'US',
                'email'      => 'john.doe@example.com',
                'phone'      => '(555) 555-5555',
            ],*/
            'line_items' => [
                [
                    'product_id' => env('WOOCOMMERCE_PRODUCT_ID'),
                    'quantity'   => 1,
                ]
            ],
        ];
        //dd($data);
        //$woo_order = Order::create($data);
        //dd($woo_order);
        $LockerOrder = LockerOrder::create([
            'raspberry_device_id' => $device_id,
            'gpio_port'           => $port,
            'woo_order_open'      => 777,
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

        

        $unlock_link = env('APP_URL') . '/unlock/' . md5($LockerOrder->id);

        $qrcode = (new QRCode($options))->render($unlock_link);
        
        Cache::forget('device_' . $device_id);


        return view('locker.order.started', ['device' => $RaspberryDevice, 'order_id' => $LockerOrder->id, 'qr' => $qrcode]);
    }


    public function unlock_order($order_id)
    {
        $LockerOrder = LockerOrder::whereRaw("md5(id) =  '{$order_id}'")->first();

        //dd($LockerOrder);
        if ($LockerOrder->closening_paid_at != null) {
            abort(404);
        }
        $LockerOrder->closening_paid_at = now();
        
        $LockerOrder->save();
        sleep(20);

        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $LockerOrder->raspberry_device_id,
            'gpio_port'           => $LockerOrder->gpio_port,
            'command'             => 'available',
            'executed'            => 0
        ]);
        Cache::forget('device_' . $LockerOrder->raspberry_device_id);
        return view('locker.order.closed', []);
    }
}
