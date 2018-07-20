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
    Route::get('paramsDetail', ['uses' => 'ParamsDetailController@producer']);
    // 测试属性
    Route::get('testProperties', ['uses' => 'RabbitBaseController@testProperties']);

    // 测试消息预取Qos
    Route::get('qos', ['uses' => 'QosController@producer']);
    Route::get('prefetchCount', ['uses' => 'PrefetchCountController@producer']);

    // 测试死信
    Route::get('dlx', ['uses' => 'DlxController@producer']);
    // 测试消费者优先级
    Route::get('testCustomerPriority', ['uses' => 'PriorityConsumerController@producer']);
    // 测试消息优先级
    Route::get('msgPriority', ['uses' => 'MsgPriorityController@producer']);
    // 消息长度限制测试
    Route::get('queueLength', ['uses' => 'QueueLengthLimitController@producer']);
    // 消费者确认
    Route::get('consumerAck', ['uses' => 'ConsumerConfirmController@producer']);

    // publisher 确认
    Route::get('publisherAck', ['uses' => 'PublisherConfirmController@producer']);

    // lazy queue
    Route::get('lazyQueue', ['uses' => 'LazyQueueController@producer']);

    // 事务
    Route::get('tx', ['uses' => 'TxController@producer']);

    // cluster
    Route::get('localCluster', ['uses' => 'LocalClusterController@producer']);
});
