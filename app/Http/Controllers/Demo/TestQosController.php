<?php

namespace App\Http\Controllers\Demo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class TestQosController
 * @package App\Http\Controllers\Demo
 */
class TestQosController extends Controller
{

    /**
     * Qos测试
     */
    public function testQosProducer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('testQosExchange', 'direct', false, true, false, false, false);

        $channel->queue_declare('testQosQueue', false, true, false, false);

        $channel->queue_bind('testQosQueue', 'testQosExchange', 'routingkey');

        // 此处只用与测试, 因此暂时先不使用 "事务" 或 "发送方确认" 来保证消息的完整投递, 后面将会有例子来学习
        //
//        for ($i = 1; $i <= 10; $i++) {
        for ($i = 1; $i <= 10; $i++) {
            $msg = new AMQPMessage('Hello World!' . $i, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
            $channel->basic_publish($msg, 'testQosExchange', 'routingkey', true, false);
        }

        $channel->close();

        $connection->close();
    }
}
