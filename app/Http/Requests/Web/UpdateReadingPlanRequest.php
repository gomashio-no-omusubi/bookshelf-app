<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * クラス UpdateReadingPlanRequest
 *
 * 読書計画の新規登録時におけるバリデーションおよび認可を制御するリクエストクラスです。
 */
class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるか判定します。
     *
     * @return bool 権限がある場合はtrue、それ以外はfalse
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * リクエストに適用されるバリデーションルールを取得します。
     *
     * @return array<string, ValidationRule|array<mixed>|string> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * 定義されたバリデーションルールのエラーメッセージを取得します。
     *
     * @return array<string, string> エラーメッセージの配列
     */
    public function messages(): array
    {
        return [
            'target_date.required' => '期日を選択してください。',
            'target_date.date' => '期日は有効な日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }
}
