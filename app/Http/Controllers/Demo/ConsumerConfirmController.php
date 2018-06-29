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

//        $set_ack_handler = function () {
//          echo "set_ack_handle";
//        };
//        $channel->set_ack_handler($set_ack_handler);
//
//        $set_return_listener = function () {
//            echo "set_return_listener";
//        };
//        $channel->set_return_listener($set_return_listener);
//
//        $set_nack_handler = function () {
//            echo "set_nack_handler";
//        };
//        $channel->set_nack_handler($set_nack_handler);

//        $channel->confirm_select();
//        $channel->confirm_select_ok();

        $channel->close();

        $connection->close();
    }
}
