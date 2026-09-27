<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageGenresTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ジャンル
     * ========================================================================= */

    // ジャンル一覧ページが正常に表示される。
    public function test_genre_index_page_displays_successfully()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.index'));
        $response->assertStatus(200);
    }

    // 認証ユーザーが新規登録フォームを表示し、正常にジャンル（genresテーブル）を追加できる。
    public function test_authenticated_user_can_view_create_form_and_store_genre()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));
        $response->assertStatus(200);

        $genreData = ['name' => '小説'];

        $response = $this->actingAs($user)->post(route('genres.store'), $genreData);

        $response->assertStatus(302);
        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['name' => '小説']);
    }

    // バリデーションエラー時は適切に処理を弾く。
    public function test_genre_store_validation_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $invalidData = ['name' => ''];

        $response = $this->actingAs($user)->post(route('genres.store'), $invalidData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    // 特定のジャンル詳細ページ（/genres/{genre}）へアクセスした際、そのジャンルに紐づいている書籍のタイトルが表示される。
    public function test_genre_show_page_displays_associated_books()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト対象の本']);

        $book->genres()->attach($genre);

        $response = $this->actingAs($user)->get(route('genres.show', $genre));
        $response->assertStatus(200);
        $response->assertSee('テスト対象の本');
    }

    // 認証ユーザーが編集フォームを正常に表示でき、ジャンル名の更新処理がDBに正しく反映される。
    public function test_authenticated_user_can_view_edit_form_and_update_genre()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧ジャンル名']);

        $response = $this->actingAs($user)->get(route('genres.edit', $genre));
        $response->assertStatus(200);

        $updateData = ['name' => '新ジャンル名'];

        $response = $this->actingAs($user)->put(route('genres.update', $genre), $updateData);

        $response->assertStatus(302);
        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '新ジャンル名',
        ]);
    }

    // 書籍が1件でも紐付いているジャンルは削除を制限し、画面に適切なエラーメッセージが表示される。
    public function test_cannot_delete_genre_that_has_associated_books()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $book->genres()->attach($genre);

        $response = $this->actingAs($user)
            ->from(route('genres.index'))
            ->delete(route('genres.destroy', $genre));

        $response->assertStatus(302);
        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas('error', 'このジャンルには書籍が紐付いているため削除できません。');
    }

    // 書籍の紐付きが一切ない（0件の）ジャンルに限り、正常に削除が実行される。
    public function test_can_delete_genre_that_has_no_associated_books()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('genres.index'))
            ->delete(route('genres.destroy', $genre));

        $response->assertStatus(302);
        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}
