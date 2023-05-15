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
    ];
}
