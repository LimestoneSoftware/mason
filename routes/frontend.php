<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontEndController;
use App\Models\Locale;
use Illuminate\Support\Facades\Schema;

Route::get('/', [FrontEndController::class, 'home'])
    ->name('home');

Route::get('/{entry:name}', [FrontEndController::class, 'entry'])
    ->where(['entry' => '[0-9A-Za-z\-]{3,255}'])
    ->name('entry');

if (Schema::hasTable('locales')) {
    $locales = Locale::all()->pluck('name')->join('|');

    Route::get('/{locale:name}', [FrontEndController::class, 'home'])
        ->where(['locale' => $locales])
        ->name('locale.home');

    Route::get('/{locale:name}/{entry:name}', [FrontEndController::class, 'entry'])
        ->where(['locale' => $locales])
        ->where(['entry' => '[0-9A-Za-z\-]{3,255}'])
        ->name('locale.entry');
}
