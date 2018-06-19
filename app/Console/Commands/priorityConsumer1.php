<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class priorityConsumer1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'priorityConsumer1';

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
        // 模拟高优先级消费者处于阻塞状态 (需要结合下面的sleep)
        $channel->basic_qos(null, 1, false);
        $channel->exchange_declare('ext_test_priority_consumer', 'direct', false, true, false);
        $channel->queue_declare('queue_test_priority_consumer', false, true, false, false);
        $channel->queue_bind('queue_test_priority_consumer', 'ext_test_priority_consumer', 'routingkey');
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            // 模拟优先级高的消费者处于阻塞状态 (需要结合上面设置的Qos, unack的数量达到Qos才会导致阻塞, 而sleep是满足该条件的条件~~~ 不要晕了..哈哈)
            sleep(5);
            // ack
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $arguments = new AMQPTable([
                'x-priority' => 12,
                'arguments1' => '写点什么吧...',
                'arguments2' => [
                    '写点什么吧...',
                    '写点什么吧...',
                ]
            ]
        );

        $channel->basic_consume(
            'queue_test_priority_consumer',
            '', false,
            false, false,
            false,
            $backCall,
            null,
            $arguments
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
