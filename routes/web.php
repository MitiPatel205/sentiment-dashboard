<?php

use App\Http\Controllers\SentimentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SentimentController::class, 'index'])->name('home');
Route::post('/analyze', [SentimentController::class, 'analyze'])->name('analyze');
Route::post('/clear-history', [SentimentController::class, 'clearHistory'])->name('clear.history');
Route::get('/export', [SentimentController::class, 'export'])->name('export');
