<?php

use App\Http\Controllers\Api\DeviceController;
use Illuminate\Support\Facades\Route;

Route::prefix('device')->group(function () {
    Route::post('fetch', [DeviceController::class, 'fetch']);
    Route::post('report', [DeviceController::class, 'report']);
});
