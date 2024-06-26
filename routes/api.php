<?php

// login : Kwiatkowski.michal1@gmail.com
// pass : Michal3

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AllegroController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ServicesStatisticsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TimeController;


use App\Http\Controllers\AdminController;

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
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'dbs'

], function ($router) {
    Route::post('/add', [ CodesController::class, 'add_db']);
    Route::get('/list', [ CodesController::class, 'list']);
    Route::get('/shortList', [ CodesController::class, 'shortList']);
    Route::get('/delete', [ CodesController::class, 'delete_db']);
    Route::get('/join', [ CodesController::class, 'join']);
    Route::post('/edit', [ CodesController::class, 'edit']);
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
    Route::get('/join', [ TemplateController::class, 'join']);
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
    Route::get('/get/offers', [ AllegroController::class, 'offers']);
    Route::get('/get/offers/off', [ AllegroController::class, 'offersOff']);
    Route::get('/get/offer', [ AllegroController::class, 'offer']);
    // monitoring
    Route::get('/monitoring/on', [ AllegroController::class, 'monitoringOn']);
    Route::get('/getLastEvent', [ AllegroController::class, 'getLastEvent']);
    Route::post('/set/monitoring', [ AllegroController::class, 'setMonitoring']);
    Route::post('/off/monitoring', [ AllegroController::class, 'offMonitoring']);
    Route::post('/monitoring/off', [ AllegroController::class, 'monitoringOff']);
    Route::get('/get/monitoring/{set}', [ AllegroController::class, 'getMonitoring']);
    // tranzakcje
    Route::get('/get/transaction', [ AllegroController::class, 'getOrders']);
    // klienci
    Route::get('/get/customers', [ AllegroController::class, 'getCustomers']);
    Route::get('/get/customer', [ AllegroController::class, 'getCustomer']);

    // glowna funkcja
    Route::get('/main_function', [ AllegroController::class, 'mainFunction']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'stat'

], function ($router) {
    // statystyki
    Route::get('/dashboard', [ StatisticsController::class, 'getDashboard']);
    Route::get('/credits', [ StatisticsController::class, 'getCredits']);
    Route::get('/cash', [ StatisticsController::class, 'getCash']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'settings'

], function ($router) {
    // ustawienia
    Route::get('/getData', [SettingsController::class, 'getPersonalData']);
    Route::post('/saveData', [SettingsController::class, 'savePersonalData']);
    Route::get('/getNotifications', [SettingsController::class, 'getNotifications']);
    Route::post('/saveNotifications', [SettingsController::class, 'saveNotifications']);
    Route::get('/getData/email', [SettingsController::class, 'getDataEmail']);
    Route::post('/setPassword', [SettingsController::class, 'setPassword']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'payment'

], function ($router) {
    // płatności
    Route::get('/history', [PaymentController::class, 'history']);
    Route::get('/pay', [PaymentController::class, 'pay']);
    Route::get('/pay-return', [PaymentController::class, 'payReturn']);
    Route::post('/pay-status/{payment_key}', [PaymentController::class, 'payStatus']);
    Route::get('/testAPI', [PaymentController::class, 'testAPI']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'testtest'

], function ($router) {
    // płatności
    Route::get('/test', [AdminController::class, 'test']);
    Route::get('/dash', [AdminController::class, 'dash']);
    // Route::get('/history', [PaymentController::class, 'history']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'time'

], function ($router) {
    Route::get('/repair_time', [ TimeController::class, 'repairTime']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'services_statistics'

], function ($router) {
    Route::get('/get_order_time_to_sent_mail', [ ServicesStatisticsController::class, 'getOrderTimeToSentMail']);
    Route::get('/make_order_time_to_sent_mail', [ ServicesStatisticsController::class, 'makeOrderTimeToSentMail']);
});