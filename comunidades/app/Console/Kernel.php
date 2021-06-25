<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CancelarPostulaciones::class,
        \App\Console\Commands\CancelarActividades::class,
        \App\Console\Commands\CancelarComunidad::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:cancelarpostulaciones')->daily();
        $schedule->command('command:cancelaractividades')->daily();
        $schedule->command('command:cancelarcomunidad')->daily();
    }
}
