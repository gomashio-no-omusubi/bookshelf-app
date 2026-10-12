<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DailyReadingPlanProcessCommandTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ■ ★応用要件：自動失効バッチ
     * ========================================================================= */

    // 毎日定刻（20:00）にバッチが実行され、目標期限（target_date）を経過した未完了の計画のステータスが自動的に「期限切れ（expired）」に変更されること
    public function test_it_automatically_expires_past_plans_at_20_00()
    {
        $this->travelTo(now()->setTime(20, 0, 0));

        $user = User::factory()->create();

        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->subDay()->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->artisan('reading-plan:daily-process')->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'status' => ReadingPlanStatus::EXPIRED,
        ]);
    }

    /* =========================================================================
     * ■ ★応用要件：リマインダーバッチ
     * ========================================================================= */

    // 目標期限3日前のユーザーを正しく抽出して通知レコード（DatabaseChannel）が作成されること
    public function test_it_sends_reminder_notification_three_days_before_deadline()
    {
        Notification::fake();
        $this->travelTo(now()->setTime(20, 0, 0));

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト対象本']);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->artisan('reading-plan:daily-process')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingReminderNotification::class,
            function ($notification, $channels) use ($book) {
                $data = $notification->toDatabase($channels['0'] ?? $notification);

                return $data['title'] === '読書計画リマインド — 期限まであと 3 日'
                    && $data['body'] === "「{$book->title}」の期限まで残り 3 日です。引き続き読書を進めましょう。";
            }
        );
    }

    // 目標期限当日のユーザーを正しく抽出して通知レコード（DatabaseChannel）が作成されること
    public function test_it_sends_reminder_notification_on_deadline_day()
    {
        Notification::fake();
        $this->travelTo(now()->setTime(20, 0, 0));

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト対象本']);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->artisan('reading-plan:daily-process')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingReminderNotification::class,
            function ($notification, $channels) use ($book) {
                $data = $notification->toDatabase($channels['0'] ?? $notification);

                return $data['title'] === '読書計画 — 本日が期限'
                    && $data['body'] === "「{$book->title}」は本日が期限です。読了済みなら完了登録を、もう少し必要なら期限を変更してください。";
            }
        );
    }

    // 期限から3日が経過した期限切れ（expired）状態の計画のユーザーを正しく抽出して通知レコードが作成されること（再エンゲージメント）
    public function test_it_sends_reengagement_notification_three_days_after_deadline()
    {
        Notification::fake();
        $this->travelTo(now()->setTime(20, 0, 0));

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト対象本']);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::EXPIRED,
        ]);

        $this->artisan('reading-plan:daily-process')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingReminderNotification::class,
            function ($notification, $channels) use ($book) {
                $data = $notification->toDatabase($channels['0'] ?? $notification);

                return $data['title'] === '読書計画 — 期限超過 3 日経過'
                    && $data['body'] === "「{$book->title}」の期限から 3 日が経過しました。読了済みなら完了登録、続けるなら期限を変更してください。";
            }
        );
    }
}
