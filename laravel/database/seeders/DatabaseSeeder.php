<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // crear roles
        // admin , client ,

        \App\Models\User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@localhost.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  // password
        ]);

        $newRecord = \App\Models\RaspberryDevice::create([
            'name'  => 'Demo Device',
            'model' => 'Raspberry PI 3B+',
        ]);
        for ($x = 0; $x <= 27; $x++) {
            $newRecord->{"gpio_{$x}_status"} = 'available';
        }
        $newRecord->save();
    }
}
