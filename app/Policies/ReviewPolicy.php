<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * クラス ReviewPolicy
 *
 * レビュー操作に関する権限を管理するポリシー。
 */
class ReviewPolicy
{
    /**
     * ユーザーが対象レビューの編集画面を表示できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Review  $review  操作対象のレビューオブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function edit(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * ユーザーが対象レビューの情報を更新できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Review  $review  操作対象のレビューオブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * ユーザーが対象レビューを削除できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  Review  $review  操作対象のレビューオブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
