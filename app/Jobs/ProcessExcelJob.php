<?php

namespace App\Jobs;

use App\Services\ExcelService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessExcelJob implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public function __construct(protected string $filePath) {}

    public function handle(ExcelService $excelService): void
    {
        $excelService->processExcelFile(storage_path('app/' . $this->filePath));
    }
}
