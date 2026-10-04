<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DailyReadingPlanProcessCommand extends Command
{
    /** @var string コマンドのシグネチャ */
    protected $signature = 'reading-plan:daily-process';

    /** @var string コマンドの説明 */
    protected $description = '期限切れ読書計画の自動失効処理および日数に応じたリマインダー通知の送信をトランザクション下で行います';

    /**
     * コマンドの実行ロジック
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::today();

        DB::transaction(function () use ($today) {

            ReadingPlan::where('status', ReadingPlanStatus::IN_PROGRESS)
                ->where('target_date', '<', $today)
                ->update(['status' => ReadingPlanStatus::EXPIRED]);

            $daysMap = collect([
                0 => '本日が読書計画の目標期日です。',
                3 => '読書計画の目標期日まで残り3日です。',
                7 => '読書計画の目標期日まで残り7日です。',
            ]);

            $targetDates = $daysMap->keys()->map(function ($days) use ($today) {
                return $today->copy()->addDays($days)->toDateString();
            });

            $plans = ReadingPlan::where('status', ReadingPlanStatus::IN_PROGRESS)
                ->whereIn('target_date', $targetDates->toArray())
                ->with(['user', 'book'])
                ->get();

            $plans->each(function ($plan) use ($today, $daysMap) {
                $diffDays = $today->diffInDays(Carbon::parse($plan->target_date), false);

                if ($daysMap->has($diffDays)) {
                    $notificationData = [
                        'book_title' => $plan->book->title,
                        'message' => $daysMap->get($diffDays),
                        'target_date' => $plan->target_date->toDateString(),
                    ];

                    Notification::send($plan->user, new ReadingReminderNotification($notificationData));
                }
            });
        });

        $this->info('読書計画の自動失効およびNotificationファサードによるリマインダー通知送信が正常に完了しました。');
    }
}
