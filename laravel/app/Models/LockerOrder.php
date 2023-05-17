<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockerOrder extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'locker_orders';

    protected $fillable = [
        'raspberry_device_id',
        'gpio_port',
        'woo_order_open',
        'woo_order_closed',
        'opening_paid_at',
        'closening_paid_at'
    ];
}
