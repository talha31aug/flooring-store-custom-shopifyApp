<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ShopifyController::class, 'index'])->middleware(['verify.shopify'])->name('home');

Route::get('/custom-api', [ShopifyController::class, 'customAPI'])->name('custom-api');

Route::get('/custom-webhook', [ShopifyController::class, 'customWebHook'])->name('custom-customWebHook');

// Route::get('/custom-api-delete-product', [ShopifyController::class, 'customAPIDeleteProduct'])->name('custom-api-delete-product');

// Route::post('/custom-api-delete-product', [ShopifyController::class, 'customAPIDeleteProduct'])->name('custom-api-delete-product');


Route::get('/login', function () {
    if (Auth::user()) {
        return redirect()->route('home');
    }
    // return view('login');
    return redirect()->route('custom-api');
})->name('login');

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
