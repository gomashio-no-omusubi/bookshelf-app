<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * クラス StoreGenreRequest
 *
 * ジャンルの新規登録時におけるバリデーションおよび認可を制御するリクエストクラスです。
 */
class StoreGenreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('genres')],
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
            'name.required' => 'ジャンル名は必須です。',
            'name.string' => 'ジャンル名は文字列で入力してください。',
            'name.max' => 'ジャンル名は255文字以内で入力してください。',
            'name.unique' => 'そのジャンル名は既に使用されています。',
        ];
    }
}
