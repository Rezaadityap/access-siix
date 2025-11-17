<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $level = ['SOP', 'Leader', 'Supervisor', 'Manager'];

        foreach ($level as $l) {
            Level::create([
                'level_name' => $l
            ]);
        }
    }
}
