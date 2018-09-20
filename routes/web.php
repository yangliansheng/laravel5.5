<?php

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

Route::get('/{address}', function ($address) {
    return redirect('/');
});
Route::post('login','Common\LoginController@login')->middleware('throttle:20,1');
Route::post('loginout', 'Common\LoginController@logout')->name('logout')->middleware('token');

Route::post('refresh_token', 'Common\LoginController@refresh_token')->name('refresh_token')->middleware('token');

Route::post('get_token', 'Common\LoginController@get_token')->name('get_token')->middleware('token');
