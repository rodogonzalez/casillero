<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessQueue extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $table = 'raspberry_device_queue';


    protected $fillable = [
            'raspberry_device_id',
            'gpio_port',
            'command',
            'executed',             
            'locker_orders_id'
        ];
}
