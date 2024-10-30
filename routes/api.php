<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

Passport::routes();

Route::post('/oauth/verify', [AuthController::class,'verify']);