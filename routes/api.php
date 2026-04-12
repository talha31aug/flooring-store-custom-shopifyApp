<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;

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

Route::post('/custom-api', [ShopifyController::class, 'customAPI']);

Route::post('/custom-api-create-product', [ShopifyController::class, 'customAPICreateProduct']);

Route::post('/custom-api-delete-product-TZ', [ShopifyController::class, 'customAPIDeleteProduct']);

Route::post('/custom-api-create-product-session', [ShopifyController::class, 'customAPICreateProductWithSession']);
Route::post('/custom-api-delete-session-products', [ShopifyController::class, 'customAPIDeleteSessionProducts']);

// Add these routes
Route::post('/custom-api-cleanup-old-products', [ShopifyController::class, 'cleanupOldSessionProducts']);
Route::post('/custom-api-cleanup-all-products', [ShopifyController::class, 'cleanupAllSessionProducts']);