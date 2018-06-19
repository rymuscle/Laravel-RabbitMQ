<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class testQosConsumerPrefetchCount1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testQosConsumerPrefetchCount1';

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
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        // 当你尝试设置prefetch_size大于0时,将会提示你 "NOR_IMPLEMENTED - prefetch_size!=0" 即 该参数rabbitmq暂未实现
        $channel->basic_qos(0, 100, false);
        $channel->exchange_declare('testQosExchange', 'direct', false, true, false);
        $channel->queue_declare('testQosQueue', false, true, false, false);
        $channel->queue_bind('testQosQueue', 'testQosExchange', 'routingkey');
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            sleep(5);
            // ack
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $channel->basic_consume('testQosQueue', '', false, false, false, false, $backCall);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
