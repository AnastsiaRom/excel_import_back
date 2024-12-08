<?php

use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\RowController;

use Illuminate\Support\Facades\Route;


Route::post('import-excel', [ExcelImportController::class, 'import'])->middleware('auth.basic');
Route::get('rows', [RowController::class, 'index']);
