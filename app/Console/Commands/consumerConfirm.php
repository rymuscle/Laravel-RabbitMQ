<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class consumerConfirm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumerConfirm';

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
        $channel->exchange_declare('consumerConfirmExt', 'direct', false, true, false);
        $channel->queue_declare('consumerConfirmQueue', false, true, false, false);
        $channel->queue_bind('consumerConfirmQueue', 'consumerConfirmExt', 'routingkey');
        $consumer_tag = 'consumer_tag';

        $backCall = function ($message) use ($channel, $consumer_tag) {
            $msg = $message->body;
            echo "\n--------\n";
            echo $msg;
            echo "\n--------\n";
            switch($msg) {
                case 'ack' :
                    // basic_ack 是一个 Positively Ack
                    // 当此处是自己设置的一个unknown delivery tag, 则会报错
                    //$message->delivery_info['channel']->basic_reject(100, true);
                    // $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                    // 也可以把channel传进回调函数中, 直接使用$channel
                    $channel->basic_ack($message->delivery_info['delivery_tag'], false);
                    break;
                case 'reject' :
                    // basic_nack, basic_reject 是 Negative Ack
                    // 如果只有一个消费者, 你还设置了消息重排, 那就糟糕了, 会无限循环
                    // $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag']);
                    $channel->basic_reject($message->delivery_info['delivery_tag'], false);
                    break;
                case 'nack' :
                    // basic_nack, basic_reject 是 Negative Ack
                    // 如果只有一个消费者, 你还设置了消息重排, 那就糟糕了, 会无限循环
                    //$message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
                    $channel->basic_nack($message->delivery_info['delivery_tag'], false, false);
                    break;
                case 'quit-cancel' :
                    $channel->basic_cancel($consumer_tag);
                    break;
                default:
                    break;
            }
        };

        $channel->basic_consume('consumerConfirmQueue', $consumer_tag, false, false, false, false, $backCall);

        register_shutdown_function([$this, 'shutdown'], $channel, $connection);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    public function shutdown($channel, $connection)
    {
        $channel->close();
        $connection->close();
    }
}
