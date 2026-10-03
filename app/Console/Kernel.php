<?php

namespace App\Console;

use App\Console\Commands\DailyReadingPlanProcessCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのカスタムArtisanコマンドの登録
     *
     * @var array
     */
    protected $commands = [
        DailyReadingPlanProcessCommand::class,
    ];

    /**
     * アプリケーションのコマンドスケジュールの定義
     *
     * @param  Schedule  $schedule
     */
    protected function schedule($schedule)
    {
        $schedule->command('reading-plan:daily-process')->daily();
    }

    /**
     * アプリケーションのクロージャベースのコマンド登録
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
