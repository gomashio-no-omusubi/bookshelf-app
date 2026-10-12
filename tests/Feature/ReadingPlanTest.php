<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ■ ★応用要件：読書計画
     * ========================================================================= */

    // ゲストは読書計画一覧へアクセスできず、ログイン画面へ強制リダイレクトされること
    public function test_guest_cannot_access_reading_plans_index_and_redirects_to_login()
    {
        $response = $this->get(route('reading-plans.index'));
        $response->assertRedirect(route('login'));
    }

    // ログインユーザーの読書計画一覧が正常に全件表示（ページネーションなし）されること
    public function test_logged_in_user_can_see_all_reading_plans_without_pagination()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        for ($i = 0; $i < 30; $i++) {
            $book = Book::factory()->create();
            ReadingPlan::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reading-plans.index'));
        $response->assertStatus(200);
    }

    // ステータスを指定した際、選択した状態の計画のみが正しく表示されること
    public function test_reading_plans_can_be_filtered_by_status()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $data);
        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::IN_PROGRESS->value,
        ]);
    }

    // 必要なデータを入力して新規計画を登録でき、読書計画一覧画面へリダイレクトされること
    public function test_user_can_create_reading_plan_with_valid_data()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $data);
        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);
    }

    // 不適切な入力値による登録がバリデーションで適切にブロックされること
    public function test_reading_plan_creation_is_blocked_by_validation_errors()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), []);
        $response->assertSessionHasErrors(['book_id', 'target_date']);
    }

    // 同一ユーザーが同じ書籍に対して進行中（in_progress）の計画を既に持っている場合は処理を拒否すること
    public function test_duplicate_in_progress_reading_plan_is_rejected()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $data);
        $response->assertStatus(302);
    }

    /* =========================================================================
     * ■ ★応用要件：読書計画（期限変更）
     * ========================================================================= */

    // 計画の所有者本人のみが期日を更新でき、読書計画一覧画面へリダイレクトされること
    public function test_owner_can_update_target_date()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $newDate = now()->addDays(10)->format('Y-m-d');

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan->id), [
            'target_date' => $newDate,
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $newDate,
        ]);
    }

    // 編集時、自身の計画を除外した形で、進行中（in_progress）の計画の重複登録を制御すること
    public function test_owner_can_update_same_plan_without_triggering_duplicate_control()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan->id), [
            'target_date' => now()->addDays(12)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
    }

    // 対象計画のステータスが「完了済み」の場合、所有者であっても編集・更新アクセスが 403 Forbidden となること
    public function test_completed_plan_cannot_be_updated_even_by_owner()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $plan->id));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan->id), [
            'target_date' => now()->addWeek()->format('Y-m-d'),
        ]);
        $response->assertStatus(403);
    }

    // 他ユーザーによる計画の編集画面アクセスおよび更新リクエストが 403 Forbidden となること
    public function test_other_user_cannot_access_edit_and_update()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $response = $this->actingAs($otherUser)->get(route('reading-plans.edit', $plan->id));
        $response->assertStatus(403);

        $response = $this->actingAs($otherUser)->put(route('reading-plans.update', $plan->id), [
            'target_date' => now()->addWeek()->format('Y-m-d'),
        ]);
        $response->assertStatus(403);
    }

    // 所有者が「読了する」を実行した際、ステータスが更新され、読書計画一覧画面へリダイレクトされること
    public function test_owner_can_complete_reading_plan()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $plan->id));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Completed,
        ]);
    }

    // 所有者本人のみが計画を削除でき、データベースからレコードが削除されること
    public function test_owner_can_delete_reading_plan()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $plan->id));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    // 他ユーザーによる計画の「完了」リクエストおよび削除リクエストが 403 Forbidden となること
    public function test_other_user_cannot_complete_or_delete_reading_plan()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $owner->id, 'status' => ReadingPlanStatus::IN_PROGRESS]);

        $response = $this->actingAs($otherUser)->post(route('reading-plans.complete', $plan->id));
        $response->assertStatus(403);

        $response = $this->actingAs($otherUser)->delete(route('reading-plans.destroy', $plan->id));
        $response->assertStatus(403);
    }
}
