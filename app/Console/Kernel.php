<?php

namespace App\Console;

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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

     //yang dijalankan di windows sheduller
    //  php artisan schedule:run 1>> NUL 2>&1

    protected function schedule(Schedule $schedule)
    {
    //  $schedule->command('backup:clean')->daily()->at(env('CLEANUP_TIME'));
    //  $schedule->command('backup:run')->daily()->at(env('BACKUP_TIME'));

    //  $schedule->command('backup:clean')->daily()->at('13:00');
    //  $schedule->command('backup:run')->daily()->at('14:00');

      $schedule->command('backup:clean')->everyMinute();
      $schedule->command('backup:run')->everyMinute();

        // $schedule->command('inspire')
        //          ->hourly(); //everyMinute
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
