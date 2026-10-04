<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

/**
 * クラス NotificationController
 *
 * 画面（Blade）向けの通知管理に関する画面表示および処理を行うコントローラーです。
 */
class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知一覧画面を表示する
     *
     * @param  Request  $request  リクエストオブジェクト
     * @return View 通知一覧ビュー
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 指定された単一の通知を既読にする
     *
     * @param  string  $id  通知ID (UUID)
     * @return RedirectResponse リダイレクトレスポンス
     */
    public function read(string $id): RedirectResponse
    {
        $notification = DatabaseNotification::findOrFail($id);

        $this->authorize('update', $notification);

        $notification->markAsRead();

        return redirect()->back()->with('flash_message', '通知を既読にしました。');
    }
}
