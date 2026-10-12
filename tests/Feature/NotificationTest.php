<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ReadingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ■ ★応用要件：通知機能
     * ========================================================================= */

    // ログインユーザーの通知一覧が正常に全件表示（ページネーションなし）されること
    public function test_logged_in_user_can_see_all_notifications_without_pagination()
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 50; $i++) {
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => ReadingReminderNotification::class,
                'data' => ['title' => 'テスト通知タイトル'.$i, 'message' => '本文'],
                'read_at' => null,
            ]);
        }

        $response = $this->actingAs($user)->get(route('notifications.index'));
        $response->assertStatus(200);

        for ($i = 1; $i <= 50; $i++) {
            $response->assertSee('テスト通知タイトル'.$i);
        }
    }

    // 通知の所有者本人のみが「既読にする」を実行でき、通知一覧画面へリダイレクトされること
    public function test_owner_can_mark_notification_as_read_and_redirects_to_index()
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => ReadingReminderNotification::class,
            'data' => ['title' => '既読テスト', 'message' => '本文'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->from(route('notifications.index'))
            ->post(route('notifications.read', ['id' => $notification->id]));

        $response->assertRedirect(route('notifications.index'));

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    // 他ユーザーによる通知の既読化リクエストが 403 Forbidden となること
    public function test_other_user_cannot_mark_notification_as_read_and_returns_403()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = $owner->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => ReadingReminderNotification::class,
            'data' => ['title' => '他人の通知', 'message' => '本文'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($otherUser)->post(route('notifications.read', ['id' => $notification->id]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }
}
