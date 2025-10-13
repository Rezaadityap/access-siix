<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nik' => '25106221',
            'name' => 'Reza Aditya Pratama',
            'password' => Hash::make('25106221'),
            'email' => 'reza@gmail.com',
            'employee_id' => 1091
        ]);
    }
}
