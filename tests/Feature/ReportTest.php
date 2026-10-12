<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ■ ★応用要件：マイ読書レポート
     * ========================================================================= */

    // 未ログインゲストはレポート画面へアクセスできず、ログイン画面へ強制リダイレクトされること
    public function test_guest_cannot_access_report_page_and_redirects_to_login()
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    // ログインユーザーのレビューに基づき、総レビュー数、読了冊数、平均評価、星別の評価分布、高評価書籍、ジャンル別評価傾向の統計データが正しく算出され、画面に表示されること
    public function test_logged_in_user_can_see_correct_reading_report_statistics()
    {
        $user = User::factory()->create();

        $genreBusiness = Genre::factory()->create(['name' => 'ビジネス']);
        $genreNovel = Genre::factory()->create(['name' => '小説']);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book1->genres()->attach($genreBusiness->id);
        $book2->genres()->attach($genreNovel->id);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => now()->format('Y-m-d'),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => now()->format('Y-m-d'),
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSee('総レビュー数');
        $response->assertSee('読了冊数');
        $response->assertSee('平均評価');
        $response->assertSee('評価分布');
        $response->assertSee('高評価書籍');
        $response->assertSee('ジャンル別評価傾向 TOP5');

        $response->assertSee('4.5');
    }
}
