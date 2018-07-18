<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class qosConsumer2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qosConsumer2';

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
        $channel->exchange_declare('qosExchange', 'direct', false, true, false);
        $channel->queue_declare('qosQueue', false, true, false, false);
        $channel->queue_bind('qosQueue', 'qosExchange', 'routingkey');

        // 6
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            // 模拟处理能力差
            sleep(5);
            // ack
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
        $channel->basic_consume('qosQueue', '', false, false, false, false, $backCall);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
