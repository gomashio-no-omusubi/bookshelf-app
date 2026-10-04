<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * アプリケーションの例外ハンドラークラス
 */
class Handler extends ExceptionHandler
{
    /**
     * バリデーション例外発生時にセッションへ保存しない入力項目
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * 例外処理のコールバックを登録します。
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'このアクションの実行は認可されていません。',
                    'error' => 'Forbidden',
                ], 403);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => '指定されたデータが見つかりませんでした。',
                    'error' => 'Not Found',
                ], 404);
            }
        });
    }

    /**
     * 未認証ユーザーがアクセスした際の処理（401 Unauthorized）
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'message' => '認証されていません。トークンが無効または設定されていません。',
                'error' => 'Unauthorized',
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
