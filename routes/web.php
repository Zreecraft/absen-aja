<?php

use Illuminate\Support\Facades\Route;

// Sajikan app.blade.php untuk semua URL kecuali yang diawali /api
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
