<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

/**
 * クラス BookPolicy
 *
 * 書籍操作に関する権限を管理するポリシー。
 */
class BookPolicy
{
    /**
     * ユーザーが対象書籍の編集画面を表示できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Book  $book  操作対象の書籍オブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function edit(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * ユーザーが対象書籍の情報を更新できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Book  $book  操作対象の書籍オブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * ユーザーが対象書籍を削除できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Book  $book  操作対象の書籍オブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
