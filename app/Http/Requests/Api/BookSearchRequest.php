<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookSearchRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
            'keyword.string' => '検索キーワードは文字列で入力してください',
            'keyword.max' => '検索キーワードは255文字以内で入力してください',
            'genre_id.integer' => 'ジャンルIDは数値で指定してください',
            'genre_id.exists' => '指定されたジャンルは存在しません',
            'page.integer' => 'ページ番号は数値で指定してください',
            'page.min' => 'ページ番号は1以上で指定してください',
            'per_page.integer' => 'ページあたりの表示件数は数値で指定してください',
            'per_page.min' => 'ページあたりの表示件数は1以上で指定してください',
            'per_page.max' => 'ページあたりの表示件数は100件以内で指定してください',
        ];
    }
}
