<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

            for ($x = 0; $x <= 27; $x++) {
                $table->enum("gpio_{$x}_status", ['available', 'in-use', 'out-of-service'])->nullable();
            }
            $table->timestamps();
        });

        Schema::create('raspberry_device_queue', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('raspberry_device_id');

            $table->bigInteger('locker_orders_id')->nullable();
            $table->string('gpio_port')->nullable();
            $table->enum("command", ['available', 'in-use', 'out-of-service'])->nullable();
            $table->boolean('executed');
            $table->timestamps();
        });

        Schema::create('locker_orders', function (Blueprint $table) {
            $table->id();           
            
            $table->bigInteger('raspberry_device_id');
            $table->string('gpio_port')->nullable();
            $table->string('woo_order_id')->nullable();
            $table->string('woo_order_open')->nullable();
            $table->string('woo_order_closed')->nullable();
            $table->timestamp('opening_paid_at')->nullable();
            $table->timestamp('closening_paid_at')->nullable();
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
