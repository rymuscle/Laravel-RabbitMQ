<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TestPriorityConsumerController
 * @package App\Http\Controllers\Demo
 */
class TestPriorityConsumerController extends Controller
{

    /**
     *
     */
    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('ext_test_priority_consumer', 'direct', false, true, false, false, false);

        $channel->queue_declare('queue_test_priority_consumer', false, true, false, false);

        $channel->queue_bind('queue_test_priority_consumer', 'ext_test_priority_consumer', 'routingkey');

        $msg = new AMQPMessage('Hello World!' , ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);

        $channel->basic_publish($msg, 'ext_test_priority_consumer', 'routingkey', false, false);

        $channel->close();

        $connection->close();
    }
}
