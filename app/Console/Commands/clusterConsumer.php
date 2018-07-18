<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class clusterConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clusterConsumer';

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
        $connection = new AMQPStreamConnection('127.0.0.1', 5673, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->exchange_declare('exc', 'direct', false, true, false);
        $channel->queue_declare('queue', false, true, false, false);
        $channel->queue_bind('queue', 'exc', 'routingkey');
        $backCall = function ($message) {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";
            echo $message->delivery_info['delivery_tag'];
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
        $channel->basic_consume('queue', '', false, false, false, false, $backCall);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
