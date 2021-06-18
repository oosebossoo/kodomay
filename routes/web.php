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
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TemplateController;

Route::get('/', function () {
    return ["1" => "jeden", "2" => "dwa"];
});

Route::get('/home', function () {
    if (Auth::check()) {
        return ["name" => Auth::user()->name, "email" => Auth::user()->email];
    }
    return "nie działa logowanie";
    //return redirect('/login');
});

Route::get('/add_allegro_user', [AllegroController::class, 'addAllegroUser']);
Route::get('/get_token', [AllegroController::class, 'getToken']);
Route::get('/delete_allegro_user', [AllegroController::class, 'deleteAllegroUser']);

Route::get('/me', [AllegroController::class, 'getAllegroUsers']);
Route::get('/main_function', [AllegroController::class, 'mainFunction']);
Route::get('/lst_ord_events', [AllegroController::class, 'getLastEvent']);
Route::get('/get_orders', [AllegroController::class, 'getOrders']);
Route::get('/cancel_order', [AllegroController::class, 'cancelOrder']);
Route::get('/get_customers', [AllegroController::class, 'getCustomers']);
Route::get('/get_customers_orders', [AllegroController::class, 'getCustomerOrders']);
Route::get('/get_offers', [AllegroController::class, 'getOffer']);
Route::get('/set_offer', [AllegroController::class, 'setOffer']);
Route::get('/test', [AllegroController::class, 'test']);
Route::get('/run_email', [AllegroController::class, 'runEmail']);

// Statystyki
Route::get('/stat/orders/today/count', [StatisticsController::class, 'ordersTodayCount']);
Route::get('/stat/offers/active/count', [StatisticsController::class, 'offersActiveCount']);
Route::get('/stat/cash/allegro', [StatisticsController::class, 'cashAllegro']);
Route::get('/stat/quantity/transaction_per_month', [StatisticsController::class, 'getTransactionInMonth']);
Route::get('/stat/cash/transaction/value', [StatisticsController::class, 'transactionValue']);

Route::get('/send_email_again',[MailController::class, 'sendEmailAgain']);
Route::get('/test_mail',[MailController::class, 'testMail']);

Route::post('/save_template',[TemplateController::class, 'saveTemplate']);
Route::get('/get_templates',[TemplateController::class, 'getTemplates']);
Route::get('/get_template',[TemplateController::class, 'getTemplate']);
Route::post('/edit_template',[TemplateController::class, 'editTemplate']);
Route::post('/delete_template',[TemplateController::class, 'deleteTemplate']);
// Route::put('/magre_template_to_offer',[TemplateController::class, 'magreTemplateToOffer']);

Route::get('/get_all_code', [ CodesController::class, 'getAllCode']);
Route::get('/get_name_of_DB_codes', [ CodesController::class, 'getNameOfDBCodes']);
Route::get('/get_codes_from_order', [CodesController::class, 'getCodesFromOrder']);
Route::get('/get_sellable_code', [ CodesController::class, 'getSellableCode']);
Route::get('/get_sellable_codes', [ CodesController::class, 'getSellableCodes']);
Route::get('/get_sold_codes', [ CodesController::class, 'getSoldCodes']);

Route::post('/change_status_of_code', [ CodesController::class, 'changeStatusOfCode']);
Route::get('/add_codes', [ CodesController::class, 'addCodes']);
Route::get('/magre_codes_to_offer', [ CodesController::class, 'magreCodesToOffer']);
Route::get('/add_codes_form_text_box', [ CodesController::class, 'addCodesFormTextBox']);

Route::get('/login', [AccountController::class, 'create']);
Route::post('login', [AccountController::class, 'store']);
Route::get('/logout', [AccountController::class, 'destroy']);
Route::get('/activation', [AccountController::class, 'activation']);

Route::get('/register', [RegistrationController::class, 'create']);
Route::post('register', [RegistrationController::class, 'store']);
Route::get('/register/activate', [MailController::class, 'activate']);

Route::get('/exchange_to_pln', [ExchangeController::class, 'exchangeToPln']);