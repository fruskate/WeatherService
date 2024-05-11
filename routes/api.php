<?php

use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/locations/add', [LocationController::class, 'addLocation']);
Route::get('/locations/{id}/weather', [LocationController::class, 'getAverageWeather']);

