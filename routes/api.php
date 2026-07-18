<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,25');
