<?php

namespace App\Console\Commands;

use App\Models\Aset;
use App\Models\RekomendasiAi;
use App\Services\RekomendasiAsetService;
use Illuminate\Console\Command;

class GenerateRekomendasiDummy extends Command
{
    protected $signature = 'rekomendasi:generate {prefix=DMY% : prefix kode_barang (REAL%, DMY%, dll)} {--force : generate ulang walau sudah ada hasil sukses}';

    protected $description = 'Generate rekomendasi AI (XGBoost lokal) untuk aset berdasarkan prefix kode_barang';

    public function handle(RekomendasiAsetService $service): int
    {
        $prefix = (string) $this->argument('prefix');
        $asetIds = Aset::where('kode_barang', 'like', $prefix)->pluck('id');
        $count = $asetIds->count();

        if ($count === 0) {
            $this->warn("Tidak ada aset dengan kode_barang LIKE '{$prefix}'.");
            return self::SUCCESS;
        }

        $sudah = RekomendasiAi::where('status', 'sukses')
            ->whereIn('aset_id', $asetIds)
            ->pluck('aset_id')
            ->all();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $generated = 0;
        $skipped = 0;
        foreach (Aset::whereIn('id', $asetIds)->cursor() as $aset) {
            if (!$this->option('force') && in_array($aset->id, $sudah, true)) {
                $skipped++;
                $bar->advance();
                continue;
            }
            $service->generate($aset, null);
            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai: {$generated} aset di-generate, {$skipped} dilewati (sudah sukses).");

        return self::SUCCESS;
    }
}