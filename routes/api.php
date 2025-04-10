<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/store', [ProductController::class, 'store']);
Route::get('/all-products', [ProductController::class, 'index']);
Route::get('/show/{id}', [ProductController::class, 'show']);
Route::post('/update', [ProductController::class, 'edit']);
Route::get('/destroy/{id}', [ProductController::class, 'destroy']);





