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
    protected $description = '期限切れ読書計画の自動失効処理、および各種タイミング（3日前・当日・3日後）に応じたリマインダー通知を行います';

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

            $targetDates = collect([
                $today->copy()->addDays(3)->toDateString(),
                $today->toDateString(),
                $today->copy()->subDays(3)->toDateString(),
            ]);

            $plans = ReadingPlan::whereIn('status', [ReadingPlanStatus::IN_PROGRESS, ReadingPlanStatus::EXPIRED])
                ->whereIn('target_date', $targetDates)
                ->with(['user', 'book'])
                ->get();

            $plans->map(function ($plan) use ($today) {
                $targetDate = Carbon::parse($plan->target_date)->startOfDay();
                $todayDate = $today->copy()->startOfDay();

                $diffDays = $todayDate->diffInDays($targetDate, false);

                $type = match ($diffDays) {
                    3 => 'before_3_days',
                    0 => 'today',
                    -3 => 'after_3_days',
                    default => null,
                };

                return $type ? [
                    'user' => $plan->user,
                    'title' => $plan->book->title,
                    'type' => $type,
                ] : null;
            })
                ->filter()
                ->groupBy('type')
                ->flatMap(fn ($groupedPlans) => $groupedPlans)
                ->map(fn ($item) => Notification::send($item['user'], new ReadingReminderNotification($item['title'], $item['type'])));
        });

        $this->info('読書計画の自動失効およびリマインダー通知の送信が正常に完了しました。');
    }
}
