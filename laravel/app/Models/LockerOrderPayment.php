<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class LockerOrderPayment extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'locker_order_payments';

    protected $fillable = [
        'locker_orders_id',
        'payment_details',        
        'amount_received',
        'transaction_id'
    ];
    
}
