<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AllegroController;

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

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'dbs'

], function ($router) {
    Route::post('/add', [ CodesController::class, 'add_db']);
    Route::get('/list', [ CodesController::class, 'list']);
    Route::get('/delete', [ CodesController::class, 'delete_db']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'db'

], function ($router) {
    Route::get('/info', [ CodesController::class, 'info']);
    Route::get('/unused', [ CodesController::class, 'unused']);
    Route::get('/used', [ CodesController::class, 'used']);
    Route::post('/add', [ CodesController::class, 'add_code']);
    Route::get('/find', [ CodesController::class, 'find']);
    Route::post('/delete', [ CodesController::class, 'delete_codes']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'templates'

], function ($router) {
    Route::get('/list', [ TemplateController::class, 'list']);
    Route::get('/get', [ TemplateController::class, 'get']);
    Route::post('/save', [ TemplateController::class, 'save']);
    Route::post('/delete', [ TemplateController::class, 'delete']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'allegro'

], function ($router) {
    // integracje
    Route::get('/test', [ AllegroController::class, 'test']);
    Route::get('/{user_id}/add', [ AllegroController::class, 'add']);
    Route::post('/delete', [ AllegroController::class, 'deleteAllegroUser']);
    Route::get('/refresh', [ AllegroController::class, 'refreshToken']);
    Route::get('/user/{user_id}/list', [ AllegroController::class, 'list']);
    // oferty
    Route::get('/monitoring/on', [ AllegroController::class, 'monitoringOn']);
    Route::get('/get/offers', [ AllegroController::class, 'offers']);
    Route::get('/get/offer', [ AllegroController::class, 'offer']);
    Route::get('/set/monitoring', [ AllegroController::class, 'setMonitoring']);
    Route::get('/get/monitoring/{set}', [ AllegroController::class, 'getMonitoring']);
    //tranzakcje
    Route::get('/get/transaction', [ AllegroController::class, 'getOrders']);

    Route::get('/main_function', [ AllegroController::class, 'mainFunction']);
});