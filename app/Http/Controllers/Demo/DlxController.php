<?php

namespace App\Http\Controllers\Demo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class DlxController
 * @package App\Http\Controllers\Demo
 */
class DlxController extends Controller
{

    /**
     *
     */
    public function testDlxProducer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('testDlxExchange', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
                'arguments1' => '想写什么信息都行',
                'arguments2' => [
                    '想写什么信息都行, 比如声明是那条业务线的',
                    '想写什么信息都行, 比如连接信息....',
                ],
                // 指定死信交换机
                'x-dead-letter-exchange' => 'testDlxExchange',
                // 指定死信路由键
//                'x-dead-letter-routing-key' => 'testDlxRoutingKey'
        ]);

        $channel->queue_declare('testDlxQueue', false, true, false, false, false, $arguments);

        $channel->queue_bind('testDlxQueue', 'testDlxExchange', 'routingkey');

        // 此处只用与测试, 因此暂时先不使用 "事务" 或 "发送方确认" 来保证消息的完整投递, 后面将会有例子来学习
        for ($i = 1; $i <= 10; $i++) {
            $msg = new AMQPMessage('Hello World!' . $i, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
            $channel->basic_publish($msg, 'testDlxExchange', 'routingkey', true, false);
        }

        $channel->close();

        $connection->close();
    }
}
