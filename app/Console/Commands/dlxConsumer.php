<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class dlxConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dlxConsumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');

        $channel = $connection->channel();

        // 创建死信交换机
        $channel->exchange_declare('testDlxExchange', 'direct', false, true, false, false, false);

        $arguments = new AMQPTable([
            'arguments1' => '想写什么信息都行',
            'arguments2' => [
                '想写什么信息都行, 比如声明是那条业务线的',
                '想写什么信息都行, 比如连接信息....',
            ],
        ]);

        // 声明队列, 并将其绑定到死信交换机上, 成为死信队列
        $channel->queue_declare('testDlxQueue', false, true, false, false, false, $arguments);
        // 设置 死信队列 与 死信交换机的 bindingKey
        $channel->queue_bind('testDlxQueue', 'testDlxExchange', 'dlxBindingkey');

        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            // 消息回应
            echo $message->delivery_info['delivery_tag'];
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $channel->basic_consume('testDlxQueue', 'consumer_tag', false, false, false, false, $backCall);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
