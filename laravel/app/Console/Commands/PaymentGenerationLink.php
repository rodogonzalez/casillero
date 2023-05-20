<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Hexters\CoinPayment\CoinPayment;


class PaymentGenerationLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $transaction['order_id'] = uniqid(); // invoice number
        $transaction['amountTotal'] = (FLOAT) 37.5;
        $transaction['note'] = 'Transaction note';
        $transaction['buyer_name'] = 'Jhone Due';
        $transaction['buyer_email'] = 'buyer@mail.com';
        $transaction['redirect_url'] = url('/back_to_tarnsaction'); // When Transaction was comleted
        $transaction['cancel_url'] = url('/back_to_tarnsaction'); // When user click cancel link
        $transaction['items'][] = [
            'itemDescription' => 'Product one',
            'itemPrice' => (FLOAT) 7.5, // USD
            'itemQty' => (INT) 1,
            'itemSubtotalAmount' => (FLOAT) 7.5 // USD
          ];
          $transaction['payload'] = [
            'foo' => [
                'bar' => 'baz'
            ]
          ];
        
          echo  CoinPayment::generatelink($transaction);        
      
    }
}
