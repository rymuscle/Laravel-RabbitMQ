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

Route::group(['namespace' => 'Demo'], function () {
    // 尝试创建第一个生产者
    Route::get('firstProducer', ['uses' => 'RabbitBaseController@firstProducer']);
    Route::get('testProperties', ['uses' => 'RabbitBaseController@testProperties']);
});
