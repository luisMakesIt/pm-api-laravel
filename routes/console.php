<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;

Route::get('/up', [HealthController::class, 'status']);
