<?php

namespace App\Console\Commands;

use App\Helpers\BlockBee;
use Hexters\CoinPayment\CoinPayment;
use Illuminate\Console\Command;
use Order;
use PaymentGateway;
use Product;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

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
    $this->throwBeeBlock();
  }

  public function throwPaypal()
  {
    $provider = new PayPalClient;

    // Through facade. No need to import namespaces
    $provider = \PayPal::setProvider();
    $provider->setCurrency('USD');

    $data = json_decode('{
      "intent": "' . env('PAYPAL_PAYMENT_ACTION') . '",
      "purchase_units": [
        {
          "amount": {
            "currency_code": "USD",
            "value": "100.00"
          }
        }
      ]
  }', true);
    // dd($data);

    $order = $provider->createOrder($data);
    dd($order);
  }

  public function throwBeeBlock()
  {
    $coin            = 'ltc';
    $my_address      = 'LZCqkNsp89vrD66Yw13QsBVL5ZnDomg5xw';
    $callback_url    = 'https://casillero.telecripto.com';
    $parameters      = ['order'=>2];
    $blockbee_params = [];
    $bb              = new BlockBee($coin, $my_address, $callback_url, $parameters, $blockbee_params, env('BLOCKBEE_API'));
    $payment_address = $bb->get_address();
    // LTC Addrr : LZCqkNsp89vrD66Yw13QsBVL5ZnDomg5xw
    dd($payment_address);
    
  }

  public function throwWoo()
  {
    $paymentGateways = PaymentGateway::all();
    //dd( $paymentGateways);
    foreach ($paymentGateways as $paymentGateway) {
      if ($paymentGateway->enabled) {
        //dd($paymentGateway);
        echo $paymentGateway->id . ' : ' . $paymentGateway->title . "\n";
      }
    }
    //dd("payments");
    //$products = Product::all()->toArray();
    // ver payment gateways

    $data = [
      'payment_method'       => 'cryptowoo',
      'payment_method_title' => 'Crypto',
      'address'              => '969 Market',
      'set_paid'             => false,
      'billing'              => [
        'first_name' => 'Unknown',
        'last_name'  => 'Unknown',
        'address_1'  => '969 Market',
        'address_2'  => '',
        'city'       => 'San Francisco',
        'state'      => 'San Jose',
        'postcode'   => '10301',
        'country'    => 'CR',
        'email'      => 'rodogonzalez@msn.com',
      ],
      'line_items' => [
        [
          'product_id' => env('WOOCOMMERCE_PRODUCT_ID'),
          'quantity'   => 3,
        ]
      ],
    ];

    $order = Order::create($data);
    dd($order, $order['payment_url']);

    //crear orden
    //dd($products,$paymentGateways);
  }

  public function CoinPayment()
  {
    //
    $transaction['order_id']     = uniqid();                     // invoice number
    $transaction['amountTotal']  = (FLOAT) 37.5;
    $transaction['note']         = 'Transaction note';
    $transaction['buyer_name']   = 'Jhone Due';
    $transaction['buyer_email']  = 'buyer@mail.com';
    $transaction['redirect_url'] = url('/back_to_tarnsaction');  // When Transaction was comleted
    $transaction['cancel_url']   = url('/back_to_tarnsaction');  // When user click cancel link
    $transaction['items'][]      = [
      'itemDescription'    => 'Product one',
      'itemPrice'          => (FLOAT) 7.5,                       // USD
      'itemQty'            => (INT) 1,
      'itemSubtotalAmount' => (FLOAT) 7.5                        // USD
    ];
    $transaction['payload'] = [
      'foo' => [
        'bar' => 'baz'
      ]
    ];

    echo CoinPayment::generatelink($transaction);
  }
}
