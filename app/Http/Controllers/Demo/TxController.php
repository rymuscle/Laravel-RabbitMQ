<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TxController
 * @package App\Http\Controllers\Demo
 */
class TxController extends Controller
{

    /**
     * 事务测试
     */
    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('txExchange', 'direct', false, true, false, false, false);

        $channel->queue_declare('txQueue', false, true, false, false);

        $channel->queue_bind('txQueue', 'txExchange', 'routingkey');

        try {
            //开启事务
            $channel->tx_select();
            // 此处是发送多条消息 (即便是一条消息, 也需要用事务来确保消息被成功被服务器接收并且如果是持久化的话还需要被成功写入硬盘)
            for ($i = 0; $i < 10; $i++) {
                $message = new AMQPMessage(
                    'msg-' . $i,
                    array(
                        'content_type' => 'text/plain',
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    ));
                $channel->basic_publish($message, 'txExchange', 'routingkey');
                sleep(1);
                // 模拟事务失败的情况 (在发送第6条消息的时候, 模拟服务器异常)
                if (5 == $i) {
                    1 / 0;  // 出现异常
                }
            }
            //提交事务
            $channel->tx_commit();
        } catch (\Exception $e) {
            $channel->tx_rollback();
        }

        $channel->close();

        $connection->close();
    }
}
