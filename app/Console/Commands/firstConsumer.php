<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class firstConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firstConsumer';

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
        // 1. 创建与Rabbit Server的TCP连接
        // 2. 创建信道
        // 3. 通过信道--创建exchange
        // 4. 通过信道--创建queue
        // 5. 通过信道--对exchange与queue进行绑定
        // 6. 创建接收消息的回调函数
        // 7. 通过信道发布消息

        // 1
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        // 2
        $channel = $connection->channel();
        // 3
        $channel->exchange_declare('ex1', 'direct', false, true, false);
        // 4
        $channel->queue_declare('queue1', false, true, false, false);
        // 5
        $channel->queue_bind('queue1', 'ex1', 'routingkey1');

        // 6
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            // 消息回应
            echo $message->delivery_info['delivery_tag'];
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
        $channel->basic_consume('queue1', '', false, false, false, false, $backCall);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
