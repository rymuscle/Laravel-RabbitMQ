<?php

namespace App\Http\Controllers\Demo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class RabbitmqBaseController
 * @package App\Http\Controllers\Demo
 */
class RabbitBaseController extends Controller
{
    // 尝试创建第一个生产者
    public function firstProducer()
    {
        // 1. 创建与Rabbit Server的TCP连接
        // 2. 创建信道
        // 3. 通过信道--创建exchange
        // 4. 通过信道--创建queue
        // 5. 通过信道--对exchange与queue进行绑定
        // 6. 创建消息
        // 7. 通过信道--发布消息

        // 1
        $connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'guest',
            'guest',
            // 默认使用的就是 / 这个vhost
            '/'
        );

        // 2
        $channel = $connection->channel();

        // 3
        $channel->exchange_declare(
            'ex1',
            'direct',

            // 如果passive设置为true:
            // rabbit-server会查看有没有名为test的exchange, 如果有就把名字什么的信息告诉你;没有就会直接报错
            // 这个参数比较鸡肋, 不过倒是可以用来检查exchange是否存在
            false,

            // 将exchange设置为持久的, 持久交换机在rabbit-server重启后会存在, 非持久的则会被清除
            // 在使用中推荐设置为 true
            true,

            // 自动删除(默认是启用的, 交换器将会在所有与其绑定的队列被删除后自动删除 (和durable无关)
            false
        );

        // 4
       $channel->queue_declare(
            // 队列名(后面如果不显示地绑定exchange与queue的话, 则默认将queue绑定到名为 (AMQP default) 的默认隐式交换机 (direct并且持久)
            'queue1',
            // 如果为true: rabbit-server会查看有没有名为hello的queue, 如果有就把名字什么的信息告诉你; 如果没有就直接报错。(这个参数比较鸡肋, 不过倒是可以用来检查queue是否存在)

            // 而false就是没有则创建, 有就什么也不做
            false,

            // true: 将queue设置为持久的, 持久队列在rabbit-server重启后会存在, 非持久的则会被清除
            // 在使用中推荐为true
            true,

            // 下面的exclusive参数: 如果设置为true, 则创建的为`排他队列`
            // 如果一个队列被声明为排他队列, 该队列仅对首次声明它的连接可见, 并在连接断开时自动删除。也就是说, 如果你在生产者中创建排他队列, 则连接结束, 队列就没了, 所以你可能一直看不到创建的队列;
            // 另外需要注意三点:
            // 1.排他队列是基于连接可见的, 同一连接的不同信道是可以同时访问同一个连接创建的排他队列的
            // 2.首次，如果一个连接已经声明了一个排他队列, 其他连接是不允许建立同名的排他队列的, 这个与普通队列不同(普通队列可以,无则创建,有则不报错)
            // 3.即使该队列是持久化的,一旦连接关闭或者客户端退出,该排他队列都会被自动删除的
            // 所以, 排他队列只能由消费者创建, 而且这种队列适用于只有一个消费者消费消息的场景
            false,

            // 自动删除(默认是启用的, 队列将会在所有的消费者停止使用之后自动删除掉自身, 注意: 没有消费者不算, 只有在有了消费之后, 所有的消费者又断开后, 就会自动删除自己, 和durable无关)
           false
        );

        // 5
        // 将queue与exchange使用 bindingkey 进行绑定
        // 绑定的时候也可以设置一些额外绑定信息
        $arguments = new AMQPTable([
                'arguments1' => '想写什么信息都行',
                'arguments2' => [
                    '想写什么信息都行, 比如声明是那条业务线的',
                    '想写什么信息都行, 比如连接信息....',
                ]
            ]
        );
        $channel->queue_bind(
            'queue1',
            'ex1',
            'routingkey1',  // 这里可以叫做bindingkey
            false,
            $arguments
        );

        // 6
        $msg = new AMQPMessage(
            // 消息实体, 使用时可以发送json
            'Hello World!',
            // 第二个数组参数稍微复杂 (可以参考:https://github.com/php-amqplib/php-amqplib/blob/b7b677d046a9735e0ad940d649feaf38d58c866c/doc/AMQPMessage.md)
            [
                // 持久化标志: 如果你要持久化消息, 则需要设置如下配置项为 2
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                // MIME content type
                'content_type' => 'text/plain'
            ]
        );

        // 7
        $channel->basic_publish(
            // 发送的消息对象
            $msg,
            // 选择的exchange路由
            'ex1',
            // routingkey 用于和 bindingkey 进行匹配 (如果没有设置routingkey或者设置的routingkey匹配不到对应的bindingkey,则消息会被Rabbit给丢弃)
            'routingkey1'
        );

        // 关闭信道
        $channel->close();
        // 关闭connection
        $connection->close();
    }

}
