<?php

namespace App\Http\Controllers;

use App\Models\RaspberryDevice;
use App\Models\LockerOrder;
use Illuminate\Http\Request;
use Codexshaper\WooCommerce\Facades\Order;

class IndexController extends Controller
{
    //
    public function request_locker($device_id)
    {
        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        $locker_ports    = [
            // the key will be stored in the db, the value will be shown as label;
            '4' => '1', 
            '9' => '2', 
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

            if (in_array($locker_current_status , ['available','','Available',null,0])    ) {
                $ports_availables[] = [$locker_port => $label];
            }

        }

        return view('device_user_view', ['device' => $RaspberryDevice, 'lockers' => $ports_availables]);

        //dd($RaspberryDevice);
        /*
            -. exploore which lockers are available oon the device
            -. assign randomly any and redirect to the payment page submitting the locker number to woo
            -. if payment successs then come back and open the locker,

        */
    }

    public function start_order($device_id, $port){

        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        $LockerOrder = LockerOrder::create([


            
        ]);


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
        
        $order = Order::create($data);




    }

}
