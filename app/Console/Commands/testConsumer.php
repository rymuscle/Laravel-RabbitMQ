<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class testConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testConsumer';

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
        //
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
}
