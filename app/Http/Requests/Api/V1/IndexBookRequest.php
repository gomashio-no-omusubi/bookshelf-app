<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * クラス IndexBookRequest
 *
 * API（V1）における書籍一覧取得時のバリデーションおよび認可を制御するリクエストクラスです。
 */
class IndexBookRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', Rule::exists('genres', 'id')],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'keyword.string' => 'キーワードは文字列で入力してください。',
            'keyword.max' => 'キーワードは255文字以内で入力してください。',

            'genre_id.integer' => 'ジャンルIDは整数で指定してください。',
            'genre_id.exists' => '選択されたジャンルIDは存在しません。',

            'page.integer' => 'ページ番号は整数で指定してください。',
            'page.min' => 'ページ番号は1以上で指定してください。',
            'per_page.integer' => 'ページあたりの表示件数は整数で指定してください。',
            'per_page.min' => 'ページあたりの表示件数は1以上で指定してください。',
            'per_page.max' => 'ページあたりの表示件数は100以内で指定してください。',
        ];
    }
}
