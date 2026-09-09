<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', Rule::exists('genres')],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'keyword.string' => 'キーワードは文字列で入力してください。',
            'keyword.max' => 'キーワードは255文字以内で入力してください。',

            'genre_id.integer' => 'ジャンルIDは整数で指定してください。',
            'genre_id.exists' => '選択されたジャンルIDは存在しません。',

            'page.integer' => 'ページ番号は整数で指定してください',
            'page.min' => 'ページ番号は1以上で指定してください',
            'per_page.integer' => 'ページあたりの表示件数は整数で指定してください',
            'per_page.min' => 'ページあたりの表示件数は1以上で指定してください',
            'per_page.max' => 'ページあたりの表示件数は100以内で指定してください',
        ];
    }
}
