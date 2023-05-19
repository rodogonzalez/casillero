<?php

namespace App\Http\Controllers;

use App\Models\LockerOrder;
use App\Models\ProcessQueue;
use App\Models\RaspberryDevice;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Order;

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

            $unlock_link = URL::signedRoute('start', ['device_id' => $device_id, 'locker_id' => $locker_port]);
            //$unlock_link = URL::temporarySignedRoute('start', now()->addSeconds(30) , ['device_id' => $device_id, 'locker_id' => $locker_port]);

            //if (in_array($locker_current_status, ['available', '', 'Available', null, 0])) {
            $ports_availables[] = [$locker_port => ['caption' => $label, 'status' => $locker_current_status , 'link' => $unlock_link]];

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

    public function start_order($device_id, $port)
    {

        if (! request()->hasValidSignature()) {

            abort(401);

        }

        $RaspberryDevice = RaspberryDevice::where('id', $device_id)->first();
        
        $LockerOrder = LockerOrder::create([
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

        $qrcode = (new QRCode($options))->render(md5($LockerOrder->id));

        return view('locker.order.started', ['device' => $RaspberryDevice, 'order_id' => $LockerOrder->id, 'qr' => $qrcode, 'url' => $unlock_link]);
    }

    public function show_open_locker_page(){
        return view('locker.order.open', []);
    }

    public function unlock_order($order_id)
    {
        $LockerOrder = LockerOrder::whereRaw("id =  '{$order_id}'")->first();

        //dd($LockerOrder);
        if ($LockerOrder->closening_paid_at != null) {
            abort(404);
        }
        $LockerOrder->closening_paid_at = now();

        $LockerOrder->save();
        //sleep(15);

        $ProcessQueue = ProcessQueue::create([
            'raspberry_device_id' => $LockerOrder->raspberry_device_id,
            'gpio_port'           => $LockerOrder->gpio_port,
            'command'             => 'available',
            'executed'            => 0
        ]);
        Cache::forget('device_' . $LockerOrder->raspberry_device_id);

        return response()->view('locker.order.closed')->header('Refresh', '5;url=/');
    }
}
