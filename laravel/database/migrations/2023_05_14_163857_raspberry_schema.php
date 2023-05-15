<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('raspberry_device', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model');
            $table->string('last_ip')->nullable();
            $table->string('email_notification')->nullable();
            $table->string('gpio_settings')->nullable();            
            $table->timestamps();
        });

        Schema::create('raspberry_device_queue', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('raspberry_device_id');
            $table->string('gpio_port')->nullable();                        
            $table->boolean('executed');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
