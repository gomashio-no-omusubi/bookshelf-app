<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRegisterRequest extends FormRequest
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
            'title' => ['required', 'string', 'max255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', Rule::unique('books')],
            'published_at' => ['required', 'date'],
            'genre_id' => ['required', Rule::exists('genres')],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'user_id' => ['required', Rule::exists('users')],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者名は必須項目です',
            'author.max' => '著者名は255文字以内で入力してください',
            'isbn.required' => 'ISBNは必須項目です',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください', // placeholder="9784000000000"の指示通り
            'isbn.unique' => 'このISBNは既に他の書籍に登録されています',
            'published_at.required' => '出版日は必須項目です',
            'published_at.date' => '有効な日付を入力してください',
            'genre_id.required' => 'ジャンルは必須項目です',
            'genre_id.exists' => '指定されたジャンルは無効です',
            'image_url.url' => '画像URLは正しいURL形式で入力してください',
            'image_url.max' => '画像URLが長すぎます',
            'user_id.required' => '登録者IDは必須項目です',
            'user_id.exists' => '指定された登録者IDは存在しません',
        ];
    }
}
