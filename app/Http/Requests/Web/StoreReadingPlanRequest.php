<?php

namespace App\Http\Requests\Web;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * クラス StoreReadingPlanRequest
 *
 * 読書計画の新規登録時におけるバリデーションおよび認可を制御するリクエストクラスです。
 */
class StoreReadingPlanRequest extends FormRequest
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
            'book_id' => ['required', 'integer', Rule::exists('books', 'id')],
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
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '書籍は整数で入力してください。',
            'book_id.exists' => '選択された書籍は存在しません。',
            'target_date.required' => '期日を選択してください。',
            'target_date.date' => '期日は有効な日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }

    /**
     * 基本バリデーション通過後に、同一書籍に対する「読書中」計画の重複チェックを行います。
     *
     * @param  Validator  $validator  バリデータオブジェクト
     * @return void 戻り値なし
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->any()) {
                return;
            }

            $exists = ReadingPlan::where('user_id', $this->user()?->id)
                ->where('book_id', $this->input('book_id'))
                ->where('status', ReadingPlanStatus::IN_PROGRESS->value)
                ->exists();

            if ($exists) {
                $validator->errors()->add('book_id', 'この書籍はすでに読書中の計画が存在します。');
            }
        });
    }
}
