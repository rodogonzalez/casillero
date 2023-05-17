<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaspberryDevice extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'raspberry_device';

    protected $fillable = [
        'name',
        'model',
        'last_ip',
        'email_notification',
        'gpio_settings',
        'gpio_0_status',
        'gpio_1_status',
        'gpio_2_status',
        'gpio_3_status',
        'gpio_4_status',
        'gpio_5_status',
        'gpio_6_status',
        'gpio_7_status',
        'gpio_8_status',
        'gpio_9_status',
        'gpio_10_status',
        'gpio_11_status',
        'gpio_12_status',
        'gpio_13_status',
        'gpio_14_status',
        'gpio_15_status',
        'gpio_16_status',
        'gpio_17_status',
        'gpio_18_status',
        'gpio_19_status',
        'gpio_20_status',
        'gpio_21_status',
        'gpio_22_status',
        'gpio_23_status',
        'gpio_24_status',
        'gpio_25_status',
        'gpio_26_status',
        'gpio_27_status',
    ];

    public function reset_gpios()
    {
        return '<a class="btn btn-sm btn-link" target="_blank" href="?reset=' . $this->id . ' "><i class="la la-search"></i> Reset GPIO</a>';
    }
}
