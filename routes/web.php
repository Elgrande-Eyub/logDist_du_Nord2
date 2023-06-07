<?php

use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleViewController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/order', [ArticleViewController::class,'index']);
Route::post('/order', [ArticleViewController::class,'store'])->name('storeOrder');
