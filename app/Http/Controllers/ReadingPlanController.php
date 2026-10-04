<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\Web\StoreReadingPlanRequest;
use App\Http\Requests\Web\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * クラス ReadingPlanController
 *
 * 画面（Blade）向けの読書計画管理に関する画面表示および処理を行うコントローラーです。
 */
class ReadingPlanController extends Controller
{
    /**
     * 読書計画の一覧画面を表示します。
     *
     * @param  Request  $request  リクエストオブジェクト
     * @return View 読書計画一覧画面のビューインスタンス
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $readingPlans = $request->user()
            ->readingPlans()
            ->with('book')
            ->withStatus($status)
            ->paginate(10);

        $currentStatus = $status;

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画の新規登録画面を表示します。
     *
     * @return View 新規登録画面のビューインスタンス
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新しい読書計画をデータベースに登録します。
     *
     * @param  StoreReadingPlanRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return RedirectResponse 読書計画一覧画面へのリダイレクトレスポンス
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $request->user()->readingPlans()->create([
            'book_id' => $request->validated()['book_id'],
            'target_date' => $request->validated()['target_date'],
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画の期日編集画面を表示します。
     *
     * @param  ReadingPlan  $plan  対象の読書計画オブジェクト
     * @return View 期日編集画面のビューインスタンス
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('edit', $plan);

        $readingPlan = $plan;

        return view('reading-plans.edit', compact('readingPlan', 'plan'));
    }

    /**
     * 読書計画の期日情報を更新します。
     *
     * @param  UpdateReadingPlanRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  ReadingPlan  $plan  更新対象の読書計画オブジェクト
     * @return RedirectResponse 読書計画一覧画面へのリダイレクトレスポンス
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update([
            'target_date' => $request->validated()['target_date'],
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画の期日を更新しました。');
    }

    /**
     * 読書計画をデータベースから削除します。
     *
     * @param  ReadingPlan  $plan  削除対象の読書計画オブジェクト
     * @return RedirectResponse 読書計画一覧画面へのリダイレクトレスポンス
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画のステータスを完了に変更します。
     *
     * @param  ReadingPlan  $plan  読了処理を行う対象の読書計画オブジェクト
     * @return RedirectResponse 読書計画一覧画面へのリダイレクトレスポンス
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => Carbon::now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読了しました。');
    }
}
