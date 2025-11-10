<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command yang harus didaftarkan secara manual (opsional)
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\PriceImportMounted::class,
    ];

    /**
     * Jadwal otomatis (cron job Laravel)
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('price:import-mounted')->dailyAt('08:00');

        // Atau kalau mau test cepat setiap 10 menit:
        // $schedule->command('price:import-mounted')->everyTenMinutes();
    }

    /**
     * Register command routes (otomatis load dari app/Console/Commands)
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // ini wajib ada supaya bisa pakai routes/console.php kalau dibutuhkan
        require base_path('routes/console.php');
    }
}
