<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PublicationController;
use App\Http\Middleware\VerificationAccountActivateMiddleware;
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

Route::prefix('v1')->middleware('auth:api')->group(function(){
    Route::prefix('auth')->group( function (){
        Route::withoutMiddleware('auth:api')->group(function(){
            Route::post('login', [AuthController::class, 'login']);
            Route::post('register', [AuthController::class, 'register']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('email/verify', [AuthController::class, 'verifyAccountEnable']);

            Route::get('email/verify/{id}', [VerificationController::class,'verify'])->name('verification.verify'); // Make sure to keep this as your route name
        });
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('email/resend', [VerificationController::class,'resend'])->name('verification.resend');

    });

    Route::prefix('publication')->group(function (){
        Route::post('create', [PublicationController::class, 'store']);
        Route::get('details/{postID}', [PublicationController::class, 'show']);
        Route::get('details/image/{imageID}', [PublicationController::class, 'showImage'])->withoutMiddleware('auth:api');
    });

    Route::middleware(VerificationAccountActivateMiddleware::class)->group(function(){
        Route::get('user', [AuthController::class, 'user']);
    });

});
