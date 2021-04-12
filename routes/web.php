<?php
// login: sebastian
// hasło: e4H~rm/jw/4n^y%=
// pl.000webhost.com
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MainController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\AllegroController;

Route::get('/', function () {
    return response()->json("1" => "jeden");
});

Route::get('/get_auth', [AllegroController::class, 'getAuth']);
Route::get('/get_token', [AllegroController::class, 'getToken']);
Route::get('/refresh_token', [AllegroController::class, 'refreshToken']);

Route::get('/me', [AllegroController::class, 'getAllegroUsers']);
Route::get('/ord_events', [AllegroController::class, 'getOrderEvents']);
Route::get('/lst_ord_events', [AllegroController::class, 'getLastOrderEvents']);
Route::get('/test', [AllegroController::class, 'checkoutForms']);
Route::get('/run_email', [AllegroController::class, 'runEmail']);

Route::get('send_mail',[MailController::class, 'sendCode']);

Route::get('/get_code', [ CodesController::class, 'getCode']);
Route::get('/get_all_code', [ CodesController::class, 'getAllCode']);
Route::get('/get_sellable_code', [ CodesController::class, 'getSellableCode']);
Route::get('/get_sold_codes', [ CodesController::class, 'getSoldCodes']);

Route::post('/change_status_of_code', [ CodesController::class, 'changeStatusOfCode']);
Route::get('/add_codes_form_text_box', [ CodesController::class, 'addCodesFormTextBox']);

Route::get('/login', [AccountController::class, 'create']);
Route::post('login', [AccountController::class, 'store']);
Route::get('/logout', [AccountController::class, 'destroy']);
Route::get('/activation', [AccountController::class, 'activation']);

Route::get('/register', [RegistrationController::class, 'create']);
Route::post('register', [RegistrationController::class, 'store']);
Route::get('/register/activate', [MailController::class, 'activate']);

Route::get('/exchange_to_pln', [ExchangeController::class, 'exchangeToPln']);