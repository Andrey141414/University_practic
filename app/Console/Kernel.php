<?php

namespace App\Console;

use App\Models\accessTokens;
use App\Models\refreshTokens;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();>= now() - interval 2 hour
        $schedule->call(function () {
            accessTokens::whereRaw('expires_at < now()')->delete();
        })->daily();

        $schedule->call(function () {
            refreshTokens::whereRaw('expires_at < now()')->delete();
        })->daily();

       

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
