<?php

use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleViewController;
use Illuminate\Support\Facades\Artisan;
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

Route::get('storage-link', function () {

$clearcache = Artisan::call('cache:clear');
echo "Cache cleared<br>";

$clearview = Artisan::call('view:clear');
echo "View cleared<br>";

$clearconfig = Artisan::call('config:cache');
echo "Config cleared<br>";

 Artisan::call('storage:link');
 echo "Storage link created successfully.";

});
