<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class LazyQueueController
 * @package App\Http\Controllers\Demo
 */
class LazyQueueController extends Controller
{

    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('lazyQueueExt', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
            'x-queue-mode' => 'lazy',
            'arguments1' => '写点什么吧...',
            'arguments2' => [
                '写点什么吧...',
                '写点什么吧...',
            ]
        ]);
        $channel->queue_declare('lazyQueue', false, true, false, false, false, $arguments);

        $channel->queue_bind('lazyQueue', 'lazyQueueExt', 'routingkey');

        $msg = new AMQPMessage('nack', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'lazyQueueExt', 'routingkey', true, false);

        $channel->close();

        $connection->close();
    }
}
