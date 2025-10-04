<?php

use App\Http\Controllers\RaceAnalysisController;
use App\Http\Controllers\RaceImportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/welcome', '/');

Route::get('/', [RaceAnalysisController::class, 'index'])->name('analysis.index');

Route::get('/import', [RaceImportController::class, 'create'])->name('import.create');
Route::post('/import', [RaceImportController::class, 'store'])->name('import.store');
