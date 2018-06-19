<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class priorityConsumer2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'priorityConsumer2';

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
        $channel->exchange_declare('ext_test_priority_consumer', 'direct', false, true, false);
        $channel->queue_declare('queue_test_priority_consumer', false, true, false, false);
        $channel->queue_bind('queue_test_priority_consumer', 'ext_test_priority_consumer', 'routingkey');
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            // ack
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $arguments = new AMQPTable([
                'x-priority' => 11,
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
