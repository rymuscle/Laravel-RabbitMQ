<?php

namespace App\Http\Controllers\Demo;

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
     * 测试消息过期后被丢进死信
     */
    public function producer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('businessExc', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
            'arguments1' => '想写什么信息都行',
            'arguments2' => [
                '想写什么信息都行, 比如声明是那条业务线的',
                '想写什么信息都行, 比如连接信息....',
            ],
            // 设置队列中消息的过期时间
            //'x-expires' => 10000, // 失效后不会放进dlx
            //'x-message-ttl' => 50000,   // 失效后会放进dlx
            // 为队列设置死信交换
            'x-dead-letter-exchange' => 'testDlxExchange',
            // 指定死信路由键 (消息被重发的时候, 使用的路由键)
            'x-dead-letter-routing-key' => 'dlxBindingkey'
        ]);

        $channel->queue_declare('businessQueue', false, true, false, false, false, $arguments);

        // 业务队列发布消息时, 消息使用的原始bindingkey
        $channel->queue_bind('businessQueue', 'businessExc', 'businessBindingKey');

        // 如果先发布一条100s过期的消息, 再发布一条10s过期的消息, 你会发现, 10秒的消息只有在100s的消息过期后, 它才立马过期被放入dlx
        // 因为: 每条消息的过期时间不同, 如果要删除所有过期消息, 势必要扫描整个队列, 所以不如等到此消息即将被消费时再判定是否过期, 如果过期, 再进行删除;
        $msg = new AMQPMessage('Hello World!', [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'text/plain',
            'expiration' => 10000 // 失效后会放进dlx
        ]);

        // 业务队列发布消息时, 消息使用的原始bindingkey
        $channel->basic_publish($msg, 'businessExc', 'businessBindingKey', true, false);

        $channel->close();

        $connection->close();
    }
}
