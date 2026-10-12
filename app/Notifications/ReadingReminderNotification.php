<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingReminderNotification extends Notification
{
    use Queueable;

    /**
     * クリエイトニュー通知インスタンス
     */
    public function __construct(
        private readonly string $bookTitle,
        private readonly string $type
    ) {}

    /**
     * 通知チャンネルの定義（DatabaseChannelのみ採用）
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * DatabaseChannelに格納するデータの定義
     *
     * @return array{title: string, body: string}
     */
    public function toDatabase(mixed $notifiable): array
    {
        return match ($this->type) {
            'before_3_days' => [
                'title' => '読書計画リマインド — 期限まであと 3 日',
                'body' => "「{$this->bookTitle}」の期限まで残り 3 日です。引き続き読書を進めましょう。",
            ],
            'today' => [
                'title' => '読書計画 — 本日が期限',
                'body' => "「{$this->bookTitle}」は本日が期限です。読了済みなら完了登録を、もう少し必要なら期限を変更してください。",
            ],
            'after_3_days' => [
                'title' => '読書計画 — 期限超過 3 日経過',
                'body' => "「{$this->bookTitle}」の期限から 3 日が経過しました。読了済みなら完了登録、続けるなら期限を変更してください。",
            ],
        };
    }
}
