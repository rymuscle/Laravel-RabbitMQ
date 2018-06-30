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
    // 测试属性
    Route::get('testProperties', ['uses' => 'RabbitBaseController@testProperties']);

    // 测试消息预取Qos
    Route::get('testQos', ['uses' => 'TestQosController@testQosProducer']);
    // 测试死信
    Route::get('testDlx', ['uses' => 'DlxController@testDlxProducer']);
    // 测试消费者优先级
    Route::get('testCustomerPriority', ['uses' => 'TestPriorityConsumerController@producer']);
    // 测试消息优先级
    Route::get('msgPriority', ['uses' => 'MsgPriorityController@producer']);
    // 消息长度限制测试
    Route::get('queueLength', ['uses' => 'QueueLengthLimitController@producer']);
    // 消费者确认
    Route::get('consumerAck', ['uses' => 'ConsumerConfirmController@producer']);

    // publisher 确认
    Route::get('publisherAck', ['uses' => 'PublisherConfirmController@producer']);
});
