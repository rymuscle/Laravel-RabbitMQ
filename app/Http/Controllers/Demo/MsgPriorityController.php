<?php

namespace App\Http\Controllers\Demo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class MsgPriorityController
 * @package App\Http\Controllers\Demo
 */
class MsgPriorityController extends Controller
{

    /**
     * priority 测试
     */
    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('msgPriorityExc', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
                'x-max-priority' => 10,
                'arguments1' => '写点什么吧...',
                'arguments2' => [
                    '写点什么吧...',
                    '写点什么吧...',
                ]
            ]
        );

        $channel->queue_declare('msgPrioityQueue', false, true, false, false, false, $arguments);

        $channel->queue_bind('msgPrioityQueue', 'msgPriorityExc', 'routingkey');
        // 下面乱序发送10条消息
        $msg1 = new AMQPMessage('Hello World - 1', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 1]);
        $msg2 = new AMQPMessage('Hello World - 2', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 2]);
        $msg3 = new AMQPMessage('Hello World - 3', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 3]);
        $msg4 = new AMQPMessage('Hello World - 4', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 4]);
        $msg5 = new AMQPMessage('Hello World - 5', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 5]);
        $msg6 = new AMQPMessage('Hello World - 6', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 6]);
        $msg7 = new AMQPMessage('Hello World - 7', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 7]);
        $msg8 = new AMQPMessage('Hello World - 8', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 8]);
        $msg9 = new AMQPMessage('Hello World - 9', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 9]);
        $msg10 = new AMQPMessage('Hello World - 10', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain', 'priority' => 10]);
        $channel->basic_publish($msg7, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg8, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg10, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg5, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg6, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg4, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg9, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg1, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg3, 'msgPriorityExc', 'routingkey', false, false);
        $channel->basic_publish($msg2, 'msgPriorityExc', 'routingkey', false, false);

        $channel->close();

        $connection->close();
    }
}
