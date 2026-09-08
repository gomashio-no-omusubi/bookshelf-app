<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
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
        $book = $this->route('book');

        return [
            'title' => ['required', 'string', 'max255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', Rule::unique('books')->ignore($book)],
            'published_at' => ['required', 'date'],
            'genre_id' => ['required', Rule::exists('genres')],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者名を入力してください',
            'author.max' => '著者名は255文字以内で入力してください',
            'isbn.required' => 'ISBNを入力してください',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNは既に他の書籍に登録されています',
            'published_at.required' => '出版日を入力してください',
            'published_at.date' => '有効な日付を入力してください',
            'genre_id.required' => 'ジャンルを選択してください',
            'genre_id.exists' => '指定されたジャンルは存在しません',
            'image_url.url' => '画像URLは正しいURL形式（http://〜 や https://〜）で入力してください',
            'image_url.max' => '画像URLが長すぎます',
        ];
    }
}
