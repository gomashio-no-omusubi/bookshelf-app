<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

/**
 * クラス LoginRequest
 *
 * ユーザーのログイン認証時におけるバリデーションおよび認可を制御するリクエストクラスです。
 */
class LoginRequest extends FortifyLoginRequest
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
            'email' => ['required',  'email'],
            'password' => ['required'],
        ];
    }
}
