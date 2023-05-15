<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PiPHP\GPIO\GPIO;
//use PiPHP\GPIO\Pin\InputPinInterface;
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

    private function every_raise(){
        // Create a GPIO object
        $gpio = new GPIO();

        // get pending records on server to process
        $commands = \App\Models\ProcessQueue::where('status','pending')->take(4)->get();

        foreach($commands as $gpio_command ){
                        
            $port_record = \App\Models\Port::firstOrCreate([
                'port' => $gpio_command->port 
            ]);

            $gpio_record= \App\Models\ProcessQueue::firstWhere('id',$gpio_command->id);
            $this->info('Executing -> ' . $gpio_command->command . ' Port: ' . $gpio_command->port );    
            $this->info('mantener -> ' . $gpio_command->delay);    
            $gpio_record->status= 'processed';

            $port_record->status= $gpio_command->command;
            $port_record->save();

            

           // $pin = $gpio->getOutputPin($gpio_command->port);
            switch ($gpio_command->command){
                case "on":
             //       $pin->setValue(PinInterface::VALUE_LOW);
                    break;
                case "off":
                //    $pin->setValue(PinInterface::VALUE_HIGH);
                    break;
            }
            //sleep($gpio_command->delay);                       
            $gpio_record->save();                
        }

        if ($commands->count()==0) {

            $this->info('No commands queued!' );
            //$this->turn_all_off();

        }

        $ports = \App\Models\Port::all();

        foreach($ports as $port){
            
            $this->info($port->port . ' : ' . $port->status );
        }

    }


    private function turn_all_off(){
        // Create a GPIO object
        $gpio = new GPIO();
        $ports = \App\Models\Port::all();

        foreach($ports as $port){
          //  $pin = $gpio->getOutputPin($port->port);
          //  $pin->setValue(PinInterface::VALUE_HIGH);            
            $port->status='off';
            $port->save();
        }
    }


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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        // this command is executed each minute, so to keep it executing each 2 seconds , it will be using the command sleep to 
        // await , the execution of this command will take around 1 minute 
        $this->every_raise();        
        while(1!=2){
            $this->get_port_status();        
            sleep(5);
        }        
    }
}