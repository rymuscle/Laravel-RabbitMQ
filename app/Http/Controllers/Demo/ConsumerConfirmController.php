<?php

namespace App\Http\Controllers\Demo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class ConsumerConfirmController
 * @package App\Http\Controllers\Demo
 */
class ConsumerConfirmController extends Controller
{

    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('consumerConfirmExt', 'direct', false, true, false, false, false);

        $channel->queue_declare('consumerConfirmQueue', false, true, false, false);

        $channel->queue_bind('consumerConfirmQueue', 'consumerConfirmExt', 'routingkey');

        $msg = new AMQPMessage('ack', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'consumerConfirmExt', 'routingkey', true, false);

        $msg = new AMQPMessage('reject', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'consumerConfirmExt', 'routingkey', true, false);

        $msg = new AMQPMessage('nack', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'consumerConfirmExt', 'routingkey', true, false);

        $msg = new AMQPMessage('quit-cancel', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);
        $channel->basic_publish($msg, 'consumerConfirmExt', 'routingkey', true, false);

        $channel->close();

        $connection->close();
    }
}
