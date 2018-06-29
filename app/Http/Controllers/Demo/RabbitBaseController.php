<?php

namespace App\Http\Controllers\Demo;

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
        // 8. 关闭信道
        // 9. 关闭连接

        // 1
        $connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'guest',
            'guest',
            // 默认使用的就是 / 这个vhost
            '/'
        // 其余更多参数属性, 后面会一一进行学习
        );

        // 2
        $channel = $connection->channel();

        // 3
        // 声明exchange时, 可以使用AMQPTable对象来创建一些额外的说明参数
        $arguments = new AMQPTable([
                'arguments1' => '想写什么信息都行',
                'arguments2' => [
                    '想写什么信息都行, 比如声明是那条业务线的',
                    '想写什么信息都行, 比如连接信息....',
                ]
            ]
        );
        $exchange_declare_res = $channel->exchange_declare(
            'ex1',
            'direct',

            // 默认为false: rabbit-server 会查看有没有已存在的同名exchange, 没有则直接创建, 有则不会进行创建; **结果总是返回 null**
            // 如果你希望查询交换机是否存在, 而又不想在查询时创建这个交换机, 设置为true即可; **如果存在则返回NULL**, 如果交换机不存在, 则会抛出一个错误的异常
            // 这个参数比较鸡肋, 不过可以用来检查exchange是否存在 (与nowait参数无关)
            false,

            // 将exchange设置为持久的, 持久交换机在rabbit-server重启后会存在, 非持久的则会被清除
            // 在使用中推荐设置为 true
            false,

            // 自动删除(默认是启用的, 交换器将会在所有与其绑定的队列被删除后自动删除 (和durable无关)
            false,

            // 其余更多参数属性, 后面会一一进行学习
            false,
            false,
            $arguments
        );
        var_dump($exchange_declare_res);

        // 4
        $queue_declare_res = $channel->queue_declare(
        // 队列名(后面如果不显示地绑定exchange与queue的话, 则默认将queue绑定到名为 (AMQP default) 的默认隐式交换机 (direct并且持久)
            'queue1',

            // 默认为false: rabbit-server 会查看有没有已存在的同名queue, 没有则直接创建, 有则不进行创建; 无论创建与否, 结果都返回 **队列基础信息**
            // 如果你希望查询队列是否存在, 而又不想在查询时创建这个队列, 设置此为true即可; 如果存在则返回 **队列基础信息**, 如果队列不存在, 则会抛出一个错误的异常
            // 和 exchange 的 passive 参数不同的是, 此处队列声明的结果会返回 **队列基础信息**, 但是这是依赖于 `nowait` 参数, 如果nowait参数为默认值false, 则会返回, 如果为true, 则就返回null
            false,

            // true: 将queue设置为持久的, 持久队列在rabbit-server重启后会存在, 非持久的则会被清除
            // 在使用中推荐为true
            true,

            // 下面的exclusive参数: 如果设置为true, 则创建的为`排他队列`
            // 如果一个队列被声明为排他队列, 该队列仅对首次声明它的连接可见, 并在连接断开时自动删除。也就是说, 如果你在生产者中创建排他队列, 则连接结束, 队列就没了, 所以你可能一直看不到创建的队列;
            // 另外需要注意三点:
            // 1.排他队列是基于连接可见的, 同一连接的不同信道是可以同时访问同一个连接创建的排他队列的
            // 2.如果一个连接已经声明了一个排他队列, 其他连接是不允许建立同名的排他队列的, 这个与普通队列不同
            // 3.即使该队列是持久化的,一旦连接关闭或者客户端退出,该排他队列都会被自动删除的
            false,

            // 自动删除(默认是启用的, 队列将会在所有的消费者停止使用之后自动删除掉自身, 注意: 没有消费者不算, 只有在有了消费之后, 所有的消费者又断开后, 就会自动删除自己, 和durable无关)
            false

        // 其余更多参数属性, 后面会一一进行学习
        );
        var_dump($queue_declare_res);

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
        $queue_bind_res = $channel->queue_bind(
            'queue1',
            'ex1',
            'routingkey1',  // 这里可以叫做bindingkey
            false,
            $arguments
        // 其余更多参数属性, 后面会一一进行学习
        );
        var_dump($queue_bind_res);

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
                // 当然, 此处还可以设置很多其他属性
                // priority
                // 可参考 https://www.kancloud.cn/xsnet/xinshangjingyan/297806
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
        // 其余更多参数属性, 后面会一一进行学习
        );

        // 8
        $channel->close();
        // 9
        $connection->close();
    }

    // 属性测试
    public function testProperties()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('ex1', 'direct', false, true, false, false, false);

        $channel->queue_declare('queue1', false, true, false, false);

        $channel->queue_bind('queue1', 'ex1', 'routingkey1');

        $msg = new AMQPMessage('Hello World!', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);

        $wait = true;
        $returnListener = function (
            $replyCode,
            $replyText,
            $exchange,
            $routingKey,
            $message
        ) use ($wait) {
            $GLOBALS['wait'] = false;
            echo "return: ",
            $replyCode, "\n",
            $replyText, "\n",
            $exchange, "\n",
            $routingKey, "\n",
            $message->body, "\n";
        };

        // 监听没有成功路由到队列的消息
        $channel->set_return_listener($returnListener);

        $channel->basic_publish($msg, 'ex1', 'routingkey123', true, false);

        while ($wait) {
            $channel->wait();
        }

        $channel->close();

        $connection->close();
    }

    /**
     * Qos测试
     */
    public function testQosProducer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        $channel->exchange_declare('ex2', 'direct', false, true, false, false, false);

        $channel->queue_declare('queue2', false, true, false, false);

        $channel->queue_bind('queue2', 'ex2', 'routingkey2');

        $msg = new AMQPMessage('Hello World!', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'text/plain']);

        $wait = true;
        $returnListener = function (
            $replyCode,
            $replyText,
            $exchange,
            $routingKey,
            $message
        ) use ($wait) {
            $GLOBALS['wait'] = false;
            echo "return: ",
            $replyCode, "\n",
            $replyText, "\n",
            $exchange, "\n",
            $routingKey, "\n",
            $message->body, "\n";
        };

        // 监听没有成功路由到队列的消息
        $channel->set_return_listener($returnListener);

        $channel->basic_publish($msg, 'ex1', 'routingkey123', true, false);

        while ($wait) {
            $channel->wait();
        }

        $channel->close();

        $connection->close();
    }
}
