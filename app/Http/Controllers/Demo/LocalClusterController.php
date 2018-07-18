<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class LocalClusterController
 * @package App\Http\Controllers\Demo
 */
class LocalClusterController extends Controller
{
    public function producer()
    {
        $connection = new AMQPStreamConnection(
            'localhost',
            5673,
            'guest',
            'guest',
            '/'
        );
        $channel = $connection->channel();
        $arguments = new AMQPTable([
                'arguments1' => '想写什么信息都行',
                'arguments2' => [
                    '想写什么信息都行, 比如声明是那条业务线的',
                    '想写什么信息都行, 比如连接信息....',
                ]
            ]
        );
        $channel->exchange_declare(
            'exc',
            'direct',
            false,
            true,
            false,
            false,
            false,
            $arguments
        );
        $channel->queue_declare(
            'queue',
            false,
            true,
            false,
            false
        );
        $arguments = new AMQPTable([
                'arguments1' => '想写什么信息都行',
                'arguments2' => [
                    '想写什么信息都行, 比如声明是那条业务线的',
                    '想写什么信息都行, 比如连接信息....',
                ]
            ]
        );
        $channel->queue_bind(
            'queue',
            'exc',
            'routingkey',
            false,
            $arguments
        );
        $msg = new AMQPMessage(
            'Hello World!',
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'text/plain'
            ]
        );
        $channel->basic_publish(
            $msg,
            'exc',
            'routingkey'
        );
        $channel->close();
        $connection->close();
    }
}
