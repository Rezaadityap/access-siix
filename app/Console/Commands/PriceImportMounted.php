<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\PriceImportService;

class PriceImportMounted extends Command
{
    protected $signature = 'price:import-mounted 
                            {--dir= : Folder scan (default dari env PRICE_SCAN_DIR)}';

    protected $description = 'Cari dd-mm-yyyy_Result.xlsx (atau terbaru) di folder mount, lalu import (upsert by material)';

    public function handle(PriceImportService $svc)
    {
        $dir = $this->option('dir') ?: env('PRICE_SCAN_DIR', '/mnt/reza_data');
        $dir = rtrim($dir, "\\/");

        if (!File::isDirectory($dir)) {
            $this->error("Folder tidak ditemukan: {$dir}");
            return self::FAILURE;
        }

        $todayName = Carbon::now()->format('d-m-Y') . '_Result.xlsx';
        $todayPath = $dir . DIRECTORY_SEPARATOR . $todayName;

        $target = null;

        if (File::exists($todayPath)) {
            $target = $todayPath;
            $this->info("✔ File hari ini ditemukan: {$target}");
        } else {
            $pattern = $dir . DIRECTORY_SEPARATOR . '*_Result.xlsx';
            $files = glob($pattern) ?: [];

            // Filter pastikan format dd-mm-yyyy_Result.xlsx
            $files = array_values(array_filter($files, function ($f) {
                return preg_match('/[\\/\\\\]\d{6}_Result\.xlsx$/', $f);
            }));

            if (empty($files)) {
                $this->warn("Tidak ada file cocok dengan pola dd-mm-yyyy_Result.xlsx di {$dir}");
                return self::FAILURE;
            }

            // Ambil yang terbaru berdasarkan mtime
            usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
            $target = $files[0];
            $this->warn("File hari ini tidak ada. Menggunakan file terbaru: {$target}");
        }

        try {
            $res = $svc->importFromFile($target);
            $this->line("Status: {$res['status']} | Inserted: {$res['inserted']} | Updated: {$res['updated']}");
            return ($res['status'] ?? '') === 'ok' ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Gagal import: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
