<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IncomeApiController;
use App\Http\Controllers\Api\SaleApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\StockApiController;

Route::middleware('api.token.header:incomes')->match(['get', 'post'], '/incomes', IncomeApiController::class);
Route::middleware('api.token.header:sales')->match(['get', 'post'], '/sales', SaleApiController::class);
Route::middleware('api.token.header:orders')->match(['get', 'post'], '/orders', OrderApiController::class);
Route::middleware('api.token.header:stocks')->match(['get', 'post'], '/stocks', StockApiController::class);
