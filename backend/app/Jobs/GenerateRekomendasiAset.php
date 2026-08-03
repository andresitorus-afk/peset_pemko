<?php

namespace App\Jobs;

use App\Models\Aset;
use App\Services\RekomendasiAsetService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRekomendasiAset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public Aset $aset, public ?int $userId = null)
    {
    }

    public function handle(RekomendasiAsetService $service): void
    {
        $service->generate($this->aset, $this->userId);
    }
}
