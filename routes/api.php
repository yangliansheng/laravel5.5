<?php

use Illuminate\Http\Request;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::apiResource('test','TestController')->middleware('token');//测试

Route::post('/login','Common\LoginController@login')->middleware('throttle:20,1');
Route::post('/loginout', 'Common\LoginController@loginout')->name('loginout');
Route::post('/get_token', 'Common\LoginController@get_token')->middleware('token');
Route::post('/get_token', 'Common\LoginController@get_token')->middleware('token');
Route::post('/refresh_token', 'Common\LoginController@refresh_token')->middleware('token');

//企业保险公司操作路由
//Route::get('company', 'API\CompanyController@index');
//Route::get('company/create', 'API\CompanyController@create');
//Route::post('company', 'API\CompanyController@store');
//Route::get('company/{id}', 'API\CompanyController@show');
//Route::get('company/{id}/edit', 'API\CompanyController@edit');
//Route::patch('company/{id}', 'API\CompanyController@update');
//Route::delete('company/{id}', 'API\CompanyController@destroy');
Route::apiResource('company', 'API\CompanyController');
Route::get('compList', 'API\CompanyController@list');
Route::post('compCheck', 'API\CompanyController@checkCode');
Route::get('sysCompList', 'API\CompanyController@sysCompList');
Route::get('sysCompInfo/{ic_id}', 'API\CompanyController@sysCompInfo');


//企业产品相关路由
Route::apiResource('product', 'API\ProductController');
Route::get('prodList', 'API\ProductController@list');
Route::get('prodRateList', 'API\ProductController@prodRateList');
Route::get('prodRateInfo/{r_id}', 'API\ProductController@prodRateInfo');
Route::get('prodRateEdit/{p_id}', 'API\ProductController@prodRateEdit');
Route::post('prodRateSave', 'API\ProductController@prodRateSave');

//系统管理用户管理
Route::apiResource('account', 'Common\AccountController');
Route::post('account/update', 'Common\AccountController@updatePassword')->middleware('token');
