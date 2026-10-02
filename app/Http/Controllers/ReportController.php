<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポート（ダッシュボード）画面を表示します。
     *
     * @param  Request  $request  リクエストオブジェクト
     * @param  ReportService  $reportService  読書レポート集計用のサービスクラス
     * @return View マイ読書レポート画面のビューインスタンス
     */
    public function index(Request $request, ReportService $reportService): View
    {
        $stats = $reportService->generateReport($request->user());

        return view('reports.index', compact('stats'));
    }
}
