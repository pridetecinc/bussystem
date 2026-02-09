<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        // 最新順に取得、1ページ20件のページネーション
        $histories = LoginHistory::with('staff.branch')
            ->orderBy('logged_at', 'desc')
            ->paginate(20);

        return view('masters.login_histories.index', compact('histories'));
    }
}