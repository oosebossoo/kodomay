<?php
// login: sebastian
// hasło: e4H~rm/jw/4n^y%=
// pl.000webhost.com
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MainController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\AllegroController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\SettingsController;

use Carbon\Carbon;

Route::get('/', function () {
    return view('errors.403');
});

Route::get('/unauthorized', function () {
    return view('errors.403');
});

Route::get('/test_allegro', [AllegroController::class, 'testAllegro']);

Route::get('/add_allegro_user', [AllegroController::class, 'addAllegroUser']);
Route::get('/{user_id}/get_token', [AllegroController::class, 'getToken']);
Route::get('/refresh_token', [AllegroController::class, 'refreshToken']); 
Route::get('/delete_allegro_user', [AllegroController::class, 'deleteAllegroUser']);
// Route::get('/get_personal_data', [SettingsController::class, 'getPersonalData']);
// Route::post('/save_personal_data', [SettingsController::class, 'savePersonalData']);
// Route::get('/get_notification', [SettingsController::class, 'getNotification']);
// Route::post('/save_notifications', [SettingsController::class, 'saveNotifications']);

Route::get('/send_notification', [NotificationController::class, 'sendNotification']);

Route::get('/me', [AllegroController::class, 'getAllegroUsers']);
Route::get('/main_function', [AllegroController::class, 'mainFunction']);
Route::get('/lst_ord_events', [AllegroController::class, 'getLastEvent']);
Route::get('/get_orders', [AllegroController::class, 'getOrders']);
Route::get('/cancel_order', [AllegroController::class, 'cancelOrder']);
Route::get('/get_customers', [AllegroController::class, 'getCustomers']);
Route::get('/get_customers_orders', [AllegroController::class, 'getCustomerOrders']);
Route::get('/set_offer', [AllegroController::class, 'setOffer']);
Route::get('/test', [AllegroController::class, 'test']);
Route::get('/run_email', [AllegroController::class, 'runEmail']);

// Statystyki
Route::get('/stat/orders/today/count', [StatisticsController::class, 'ordersTodayCount']);
Route::get('/stat/offers/active/count', [StatisticsController::class, 'offersActiveCount']);
Route::get('/stat/cash/allegro', [StatisticsController::class, 'cashAllegro']);
Route::get('/stat/quantity/transaction_per_month', [StatisticsController::class, 'getTransactionInMonth']);
Route::get('/stat/cash/transaction/value', [StatisticsController::class, 'transactionValue']);

// Mail
Route::get('/test_send',[MailController::class, 'testSend']);
Route::get('/send_email_again',[MailController::class, 'sendEmailAgain']);
Route::get('/test_mail',[MailController::class, 'testMail']);

//Admin Panel
Route::get('/show_users',[AdminController::class, 'showUsers']);
Route::get('/edit_user',[AdminController::class, 'editUser']);
Route::get('/delete_user',[AdminController::class, 'deleteUser']);
Route::get('/send_pdf',[AdminController::class, 'sendPDF']);
Route::get('/get_excel',[AdminController::class, 'getExcel']);

// Szablony
// Route::post('/templates/save',[TemplateController::class, 'saveTemplate']);
// Route::get('/templates/list',[TemplateController::class, 'listTemplates']);
// Route::get('/templates/get',[TemplateController::class, 'getTemplate']);
// Route::post('/template/delete',[TemplateController::class, 'deleteTemplate']);
// Route::put('/magre_template_to_offer',[TemplateController::class, 'magreTemplateToOffer']);

// Kody
Route::get('/get_all_code', [ CodesController::class, 'getAllCode']);

// Route::post('/codedbs/add', [ CodesController::class, 'add']);
// Route::get('/codedbs/list', [ CodesController::class, 'list']);
// Route::post('/codedbs/delete', [ CodesController::class, 'delete']);
// Route::post('/codedbs/db/unused', [ CodesController::class, 'unused']);
// Route::post('/codedbs/db/used', [ CodesController::class, 'used']);

Route::get('/codedbs/codes/list', [CodesController::class, 'listOfCodesFromDB']);
Route::get('/codedbs/codes/delete', [CodesController::class, 'deleteCodes']);

Route::get('/get_codes_from_order', [CodesController::class, 'getCodesFromOrder']);
Route::get('/get_sellable_code', [ CodesController::class, 'getSellableCode']);
Route::get('/get_sellable_codes', [ CodesController::class, 'getSellableCodes']);
Route::get('/get_sold_codes', [ CodesController::class, 'getSoldCodes']);
Route::post('/change_status_of_code', [ CodesController::class, 'changeStatusOfCode']);
Route::get('/magre_codes_to_offer', [ CodesController::class, 'magreCodesToOffer']);
Route::get('/add_codes_form_text_box', [ CodesController::class, 'addCodesFormTextBox']);

// // Logowanie
// Route::get('/login', [AccountController::class, 'create']);
// Route::post('login', [AccountController::class, 'store']);
// Route::get('/logout', [AccountController::class, 'destroy']);
Route::post('/activation', [AccountController::class, 'activation']);
Route::post('/reset_password', [AccountController::class, 'resetPassword']);
Route::post('/reset_password_mail', [AccountController::class, 'resetPasswordMail']);

// // Rejestracja
// Route::get('/register', [RegistrationController::class, 'create']);
// Route::post('register', [RegistrationController::class, 'store']);
// Route::get('/register/activate', [MailController::class, 'activate']);

Route::get('/exchange_to_pln', [ExchangeController::class, 'exchangeToPln']);