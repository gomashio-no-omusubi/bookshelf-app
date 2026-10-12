<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

/**
 * クラス ReadingPlanPolicy
 *
 * 読書計画に関する権限を管理するポリシー。
 */
class ReadingPlanPolicy
{
    /**
     * ユーザーが対象読書計画の編集を行えるか判定します。
     * 所有者 かつ 対象計画のステータスが「完了済み（Completed）」でない場合のみ許可します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  ReadingPlan  $readingPlan  操作対象の読書計画オブジェクト
     * @return bool 所有者かつ未完了の場合はtrue、それ以外はfalse
     */
    public function edit(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    /**
     * ユーザーが対象読書計画の更新を行えるか判定します。
     * 所有者 かつ 対象計画のステータスが「完了済み（Completed）」でない場合のみ許可します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  ReadingPlan  $readingPlan  操作対象の読書計画オブジェクト
     * @return bool 所有者かつ未完了の場合はtrue、それ以外はfalse
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    /**
     * ユーザーが対象読書計画の削除を行えるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  ReadingPlan  $readingPlan  操作対象の読書計画オブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * ユーザーが対象読書計画のステータスを「読了（完了）」に変更できるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  ReadingPlan  $readingPlan  操作対象の読書計画オブジェクト
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function complete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
