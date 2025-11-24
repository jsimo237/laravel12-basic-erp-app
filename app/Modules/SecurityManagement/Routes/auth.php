<?php


use Illuminate\Support\Facades\Route;
use App\Modules\SecurityManagement\Controllers\Auth\LoginController;
use App\Modules\SecurityManagement\Controllers\Auth\LogoutController;
use App\Modules\SecurityManagement\Controllers\Auth\MeController;
use App\Modules\SecurityManagement\Controllers\Auth\OtpCodeController;
use App\Modules\SecurityManagement\Controllers\Auth\PasswordResetController;
use App\Modules\SecurityManagement\Controllers\Auth\RefreshTokenController;


Route::prefix('auth')
    ->name('bc.auth.')

    ->group( function (){

    Route::middleware(['has-auth-guard-header','guest:api'])
        ->group( function (){
            Route::post('login',[LoginController::class,'login'])->name('login');
            Route::post('otp/verify',[OtpCodeController::class,'verify'])->name('otp.verify');
            Route::post('otp/resend',[OtpCodeController::class,'resend'])->name('otp.resend');

            Route::prefix('forgot-password')->group(function () {
                Route::post('request', [PasswordResetController::class, 'request'])->name('forgot-password.request');
                Route::post('reset',   [PasswordResetController::class, 'reset'])->name('forgot-password.reset');
            });
        });

    Route::middleware('auth:api')
        ->group( function (){

            Route::get('me', MeController::class)->name('me');
            Route::post('logout', [LogoutController::class,"logout"])->name('logout');
            Route::post('logout/all-devices', [LogoutController::class,"logout"])->name('logout-all-devices');

            Route::post('refesh', RefreshTokenController::class)->name('refesh');
        });

});



