<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LockerOrderPayment;

class LockerOrder extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'locker_orders';

    protected $fillable = [
        'raspberry_device_id',
        'gpio_port',
        'woo_order_id',
        'woo_order_open',
        'woo_order_closed',
        'opening_paid_at',
        'closening_paid_at',        
        'crypto_wallet_total_amount',
        'crypto_wallet_address'
    ];

    private function differenceInHours($startdate, $enddate)
    {
        $start_datetime = new DateTime($startdate);
        $diff           = $start_datetime->diff(new DateTime(now()));

        return (object) ['years' => $diff->y, 'months' => $diff->m, 'days' => $diff->days, 'hours' => $diff->h, 'minutes' => $diff->i];
    }

    public function getDurationAttribute()
    {
        $time_used = $this->differenceInHours($this->opening_paid_at, now());

        return $time_used;
    }

    public function getTotalPaidAttribute(){

        if (is_null($this->crypto_wallet_address) ) return null ;

        return  LockerOrderPayment::whereRaw('locker_orders_id = '.$this->id . ' and payment_details like  "%' . $this->crypto_wallet_address . '%"')->sum('amount_received' );
    }

    public function getPaymentsAttribute(){
        if (is_null($this->crypto_wallet_address) ) return null ;

        /**
        {
            "address_in":"MMGZiHoYPbdVL1PcoE64BrmyygqY2miYTL",
            "address_out":"LZCqkNsp89vrD66Yw13QsBVL5ZnDomg5xw",
            "txid_in":"c6ec6673e98638edfcfa63dcc9f2482e76775a9ca7a2e6af36e3d1c20faf2575",
            "txid_out":"2db898917f5a2c3f39ef479ba6195188f38a0ad5a5d7110c2853b609bfc045dc",
            "confirmations":"1574",
            "value":"5097900",
            "value_coin":"0.050979",
            "value_forwarded":"5046710",
            "value_forwarded_coin":"0.0504671",
            "coin":"ltc",
            "pending":"0",
            "order":"2",
            "amount":"4.8",
            "uuid":"5b837ca5-f296-424b-b2e7-86e0d4bfef96",
            "fee":"50979",
            "fee_coin":"0.00050979",
            "price":"94.552562",
            "result":"sent"
        } 

         */         
        //$payments = LockerOrderPayment::whereRaw('locker_orders_id = '.$this->id)->get()->toArray();        
        $payments =  LockerOrderPayment::select(["amount_received","transaction_id"])->whereRaw('locker_orders_id = '.$this->id . ' and payment_details like  "%' . $this->crypto_wallet_address . '%"')->get()->toArray();        
        return $payments;

    }
}
