<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportExcelRequest;
use App\Jobs\ProcessExcelJob;
use App\Services\ExcelService;

class ExcelImportController extends Controller
{
    public function import(ImportExcelRequest $request)
    {
        $request->validated();

        $file     = $request->file('file');
        $filePath = $file->store('uploads');

        // (new ExcelService)->processExcelFile(storage_path('app/' . $filePath));

        ProcessExcelJob::dispatch($filePath);

        return response()->json(['message' => 'Файл загружен и обработка начата']);
    }
}
