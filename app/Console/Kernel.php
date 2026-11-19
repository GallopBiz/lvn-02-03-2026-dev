<?php

namespace App\Console;

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
        $schedule->command('leave:reset')->yearlyOn(6, 1, '00:00');
        // Auto assign sections using Round Robin during session transition (31st March / 1st April)
        $schedule->command('session:allocate-sections')->yearlyOn(3, 31, '23:59');
        $schedule->command('session:allocate-sections')->yearlyOn(4, 1, '00:01');
        // $schedule->command('inspire')->hourly();
		
		$schedule->command('fees:sync-missing-receipts')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->runInBackground();
			 
			 $schedule->call(function () {
        \Log::info('Laravel scheduler is running');
    })->everyMinute();
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
