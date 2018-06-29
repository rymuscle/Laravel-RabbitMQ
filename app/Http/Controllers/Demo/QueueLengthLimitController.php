<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * 测试队列长度限制
 * Class QueueLengthLimitController
 * @package App\Http\Controllers\Demo
 */
class QueueLengthLimitController extends Controller
{

    /**
     *
     */
    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('queueLengthLimitExc', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
                'x-max-length' => 8,
                //'x-max-length-bytes' => 88,
                'arguments1' => '写点什么吧...',
                'arguments2' => [
                    '写点什么吧...',
                    '写点什么吧...',
                ]
            ]
        );

        $channel->queue_declare('lengthLimitQueue', false, true, false, false, false, $arguments);

        $channel->queue_bind('lengthLimitQueue', 'queueLengthLimitExc', 'routingkey');

        $msg = new AMQPMessage('Hello World', [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'text/plain'
        ]);

        $channel->basic_publish($msg, 'queueLengthLimitExc', 'routingkey', false, false);

        $channel->close();

        $connection->close();
    }
}
