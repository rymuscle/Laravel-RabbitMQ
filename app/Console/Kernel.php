<?php

namespace App\Console;

use App\Console\Commands\consumerConfirm;
use App\Console\Commands\firstConsumer;
use App\Console\Commands\msgPriorityConsumer;
use App\Console\Commands\priorityConsumer1;
use App\Console\Commands\priorityConsumer2;
use App\Console\Commands\testProducter;
use App\Console\Commands\testQosConsumer1;
use App\Console\Commands\testQosConsumer2;
use App\Console\Commands\testQosConsumerPrefetchCount1;
use App\Console\Commands\testQosConsumerPrefetchCount2;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        firstConsumer::class,
        testProducter::class,
        testQosConsumer1::class,
        testQosConsumer2::class,
        testQosConsumerPrefetchCount1::class,
        testQosConsumerPrefetchCount2::class,
        priorityConsumer1::class,
        priorityConsumer2::class,
        msgPriorityConsumer::class,
        consumerConfirm::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
