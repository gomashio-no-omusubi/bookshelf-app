<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingReminderNotification extends Notification
{
    use Queueable;

    /** @var array 通知データの配列 */
    private $details;

    /**
     * コンストラクタ
     *
     * @param  array  $details  通知に含まれる詳細情報
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * 通知チャンネルの定義 (Databaseを指定)
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * notificationsテーブルのdataカラムに格納する配列を返す
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => '【'.($this->details['book_title'] ?? '書籍').'】',
            'body' => $this->details['message'] ?? '',
            'target_date' => $this->details['target_date'] ?? '-',
        ];
    }
}
