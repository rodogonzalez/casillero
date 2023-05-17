<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PiPHP\GPIO\GPIO;
//use PiPHP\GPIO\Pin\InputPinInterface;
use Illuminate\Support\Facades\Http;
use PiPHP\GPIO\Pin\PinInterface;

class process_gpio_queue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue_gpio:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command executes the commands in the remote queue for be processed locally';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function every_raise()
    {
        $delay = 15;
        $url   = env('APP_URL') . '/device-feed/' . env('RASPBERRY_DEVICE_ID');
        $this->info('connecting...  ' . $url);
        $response = Http::accept('application/json')->get($url, []);
        $commands = json_decode($response->getBody(), true);

        $gpio        = new GPIO();
        $reset_cache = false;

        foreach ($commands as $gpio_command) {
            $this->info('Executing ->  Port: ' . $gpio_command['gpio_port']);

            if (env('GPIO_AVAILABLE')) {
                $pin = $gpio->getOutputPin($gpio_command['gpio_port']);
                $pin->setValue(PinInterface::VALUE_LOW);
            } else {
                $this->info('Skipping GPIO action');
            }
            $reset_cache = true;
        }

        if ($reset_cache) {
            $this_raspberry = \App\Models\RaspberryDevice::where('id', env('RASPBERRY_DEVICE_ID'))->first();

            foreach ($commands as $gpio_command) {
                $this->info('turning off ->  Port: ' . $gpio_command['gpio_port']);

                if (env('GPIO_AVAILABLE')) {
                    $pin = $gpio->getOutputPin($gpio_command['gpio_port']);
                    $pin->setValue(PinInterface::VALUE_HIGH);
                } else {
                    $this->info('Skipping GPIO action');
                }
            }
            $ids = [];
            foreach ($commands as $gpio_command) {
                $ids[]                                                              = $gpio_command['id'];
                $this_raspberry->{'gpio_' . $gpio_command['gpio_port'] . '_status'} = $gpio_command['command'];
            }

            \App\Models\ProcessQueue::whereRaw('id in (' . implode(',', $ids) . ')')->update(['executed' => true]);

            $this_raspberry->last_ip = request()->ip();
            $this_raspberry->save();
            
            $url   = env('APP_URL') . '/reset-device-feed/' . env('RASPBERRY_DEVICE_ID');
            Http::get($url, []);
        }

        

        if (count($commands) == 0) {
            $this->info('No commands queued!');
        }

        $this->info('done -> ');
    }

    private function turn_all_off()
    {
        // Create a GPIO object
        $gpio = new GPIO();

        for ($x = 0; $x <= 27; $x++) {
            if (env('GPIO_AVAILABLE')) {
                $pin = $gpio->getOutputPin($x);
                $pin->setValue(PinInterface::VALUE_LOW);
            }
        }

        sleep(10);

        for ($x = 0; $x <= 27; $x++) {
            if (env('GPIO_AVAILABLE')) {
                $pin = $gpio->getOutputPin($x);
                $pin->setValue(PinInterface::VALUE_HIGH);
            }
        }
    }
    /*

    private function get_port_status(){


        // Create a GPIO object
        $gpio = new GPIO();


        $ports = \App\Models\Port::all();


        foreach($ports as $port){
         //   $pin = $gpio->getOutputPin($port->port);
            switch($port->status){
                case "on":
            //        $pin->setValue(PinInterface::VALUE_LOW);
                    break;
                case "off":
              //      $pin->setValue(PinInterface::VALUE_HIGH);
                    break;
            }

        }
        $this->info('updated from db ... ' );


    }
*/

    public function handle()
    {
        // this command is executed each minute, so to keep it executing each 2 seconds , it will be using the command sleep to
        // await , the execution of this command will take around 1 minute
        $this->turn_all_off();

        while (1 != 2) {
            $this->every_raise();
            sleep(15);
        }
    }
}
