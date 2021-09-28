<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CodesController;

Route::group([
    'middleware' => 'codes',
    'prefix' => 'dbs'

], function ($router) {
    Route::post('/add', [ CodesController::class, 'add']);
    Route::get('/list', [ CodesController::class, 'list']);
    Route::post('/delete', [ CodesController::class, 'delete']);
});

// Route::group([
//     'middleware' => 'codes',
//     'prefix' => 'db'

// ], function ($router) {
//     Route::get('/info', [ CodesController::class, 'info']);
//     Route::get('/unused', [ CodesController::class, 'unused']);
//     Route::get('/used', [ CodesController::class, 'used']);
//     Route::post('/add', [ CodesController::class, 'add']);
//     Route::post('/find', [ CodesController::class, 'find']);
//     Route::post('/delete', [ CodesController::class, 'codes-delete']);
// });