<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class msgPriorityConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msgPriorityConsumer';

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
        // 禁用, 1, 2, 10(10就尴尬了...)
        $channel->basic_qos(0, 1, false);
        $channel->exchange_declare('msgPriorityExc', 'direct', false, true, false);
        $arguments = new AMQPTable([
                'x-max-priority' => 10,
                'arguments1' => '写点什么吧...',
                'arguments2' => [
                    '写点什么吧...',
                    '写点什么吧...',
                ]
            ]
        );
        $channel->queue_declare('msgPrioityQueue', false, true, false, false, false, $arguments);
        $channel->queue_bind('msgPrioityQueue', 'msgPriorityExc', 'routingkey');
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
//            sleep(2);
            // ack
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $channel->basic_consume('msgPrioityQueue', '', false, false, false, false, $backCall);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
