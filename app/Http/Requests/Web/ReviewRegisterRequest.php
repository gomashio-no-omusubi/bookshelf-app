<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRegisterRequest extends FormRequest
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
            'comment' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'], // ★ 評価値（例: 星1〜5個の範囲チェック）
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => 'レビュー内容を入力してください',
            'comment.max' => 'レビュー内容は1000文字以内で入力してください',
            'rating.required' => '評価を選択してください。',
            'rating.integer' => '評価は数値で指定してください',
            'rating.between' => '評価は1から5の間で選択してください', // 範囲エラーの文言
        ];
    }
}
