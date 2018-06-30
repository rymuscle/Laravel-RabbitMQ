<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ConfirmHandlerController
 * @package App\Http\Controllers\Demo
 */
class PublisherConfirmController extends Controller
{

    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->confirm_select(false);

        // publisher confirm 和 consumer ack 机制类似, broker会响应ack或nack
        // 设置ack回调
        $set_ack_handler = function (AMQPMessage $message) {
            echo "Message acked with content " . $message->body . PHP_EOL;
        };
        $channel->set_ack_handler($set_ack_handler);

        // 手册指出,只有在负责队列的Erlang进程中发生内部错误时才会回应nack. 所以这个在测试中也一直没有使用set_nack_handler捕获到错误
        $set_nack_handler = function (AMQPMessage $message) {
            echo "Message nacked with content " . $message->body . PHP_EOL;
        };
        $channel->set_nack_handler($set_nack_handler);

        $channel->exchange_declare('publisherConfirmExt', 'direct', false, true, false, false, false);

        $channel->queue_declare('publisherConfirmQueue', false, true, false, false);

        $channel->queue_bind('publisherConfirmQueue', 'publisherConfirmExt', 'routingkey');

        $msg = new AMQPMessage('ack', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'publisherConfirmExt', 'routingkey', true, false);

        $msg = new AMQPMessage('nack', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'publisherConfirmExt', 'routingkey', true, false);

        $channel->wait_for_pending_acks();

        $channel->close();

        $connection->close();
    }
}
