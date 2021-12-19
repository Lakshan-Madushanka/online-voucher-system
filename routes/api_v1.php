<?php

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

// auth routes//
Route::name('api.')->group(function () {
    Route::post('/register', 'Auth\SanctumSPAAuthController@register')
        ->name('spa_register');

    Route::post('/spa-login', 'Auth\SanctumSPAAuthController@SPAAuth')
        ->name('spa_login');

    Route::post('/spa-logout', 'Auth\SanctumSPAAuthController@logout')
        ->middleware('auth:sanctum')
        ->name('spa_logout');
});

Route::get('/email/verify/{id}/{hash}',
    'Auth\SanctumSPAAuthController@verifyEmail')
    ->name('verification.verify');

Route::get('/email/send/{id}', 'Auth\SanctumSPAAuthController@sendEmail')
    ->name('verification.send');

Route::post('/password/forgot',
    'Auth\SanctumSPAAuthController@sendResetPasswordLink')
    ->name('password.forgot')
    ->middleware('throttle:1');

Route::get('/password/reset-redirect',
    'Auth\SanctumSPAAuthController@redirectPasswordResetPage')
    ->name('password.reset');

Route::post('/password/reset',
    'Auth\SanctumSPAAuthController@resetPassword')
    ->name('reset.password');
// end auth routes


Route::name('api.')->group(function () {

    //voucher routes
    Route::delete('vouchers/delete-many', 'Voucher\VoucherController@destroyMany')
        ->name('vouchers.delete_many');
    Route::apiResource('vouchers', 'Voucher\VoucherController');
    Route::get('vouchers/status/search',
        'Voucher\VoucherController@searchStatus')
        ->name('vouchers.search.status');
    Route::get('vouchers/price-range/search',
        'Voucher\VoucherController@searchPriceRange')
        ->name('vouchers.search.price_range');
    // end voucher routes

    //cash vouchers
    Route::delete('cash-vouchers/delete-many', 'CashVoucher\CashVoucherController@destroyMany')
        ->name('cash-vouchers.delete_many');
    Route::apiResource('cash-vouchers', 'CashVoucher\CashVoucherController');
    Route::get('cash-vouchers/price-range/search',
        'CashVoucher\CashVoucherController@searchPriceRange')
        ->name('cash-vouchers.search.price_range');

});



