<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/**
 * クラス NotificationPolicy
 *
 * 通知操作に関する権限を管理するポリシー。
 */
class NotificationPolicy
{
    /**
     * ユーザーが対象通知の既読を行えるか判定します。
     *
     * @param  User  $user  認証中のユーザーオブジェクト
     * @param  DatabaseNotification  $notification  操作対象の通知オブジェク
     * @return bool 所有者の場合はtrue、それ以外はfalse
     */
    public function update(User $user, DatabaseNotification $notification): bool
    {
        return (int) $notification->notifiable_id === (int) $user->id
            && $notification->notifiable_type === User::class;
    }
}
