<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use DateTime;
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
        'closening_paid_at',
        'woo_order_id',
        'locker_orders_id'
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
}
