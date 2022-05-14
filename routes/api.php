<?php

use App\Http\Controllers\SensorsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'api/v1', 'middleware' => 'throttle:1,1'], function () {

    Route::post('/sensors/{uuid}/measurements', [SensorsController::class, 'createMeasurement']);

});

Route::group(['prefix' => 'api/v1'], function () {

    Route::get('/sensors/{uuid}', [SensorsController::class, 'getStatus']);
    Route::get('/sensors/{uuid}/metrics', [SensorsController::class, 'getMetrics']);
    Route::get('/sensors/{uuid}/alerts', [SensorsController::class, 'getAlerts']);

});


