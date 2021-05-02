<?php

use Illuminate\Http\Request;
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


/* Place all routes that need token checking */
Route::middleware(['tokenCheck'])->group(function () {

    Route::get('/authors', 'authorsController@index');
    Route::get('/comics/{authorId}', 'authorsController@authorComics');

});
