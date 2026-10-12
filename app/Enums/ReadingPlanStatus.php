<?php

namespace App\Enums;

/**
 * 読書計画のステータスを管理するEnum
 */
enum ReadingPlanStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case Completed = 'completed';
    case EXPIRED = 'expired';

    /**
     * ステータスの日本語表示名を取得
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            self::IN_PROGRESS => '進行中',
            self::Completed => '完了',
            self::EXPIRED => '期限切れ',
        };
    }

    /**
     * 画面表示用のバッジCSSクラスを取得
     * ご自身の設計判断で問題ないため、視認性の高いスタイルを維持・定義しています。
     *
     * @return string
     */
    public function badgeClass()
    {
        return match ($this) {
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::EXPIRED => 'bg-red-100 text-red-800',
        };
    }
}
