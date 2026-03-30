<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Account;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AccountLedgerController extends Controller
{
    /**
     * Display the ledger (勘定元帳) index page.
     */
    public function index(Request $request)
    {
        $query = Account::query();
        
        // 搜索功能：科目代码、科目名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 筛选：有效状态 (可选功能)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：默认按 ID 降序 (新注册的在后)，也可改为按 code 升序
        $accounts = $query->orderBy('id', 'asc')->paginate($perPage);
        
        // 保留查询参数用于分页链接
        $accounts->appends(['search' => $request->search, 'is_active' => $request->is_active, 'per_page' => $perPage]);
        
        return view('masters.account-ledgers.index', compact('accounts'));
    }

    public function generate($id){
        $year = request()->input('year_month');

        $datas = [];
        $datas['account_name'] = "现金";
        $datas['year'] = $year;

        $entry_id = AccountJournalEntry::where('posting_date','like', $year."%")->pluck('id');
        $lines = AccountJournalLine::whereIn('journal_entry_id', $entry_id)->where('account_id',$id)->get();
        
        foreach ($lines as $line) { 
            $account_name = "XXX";
            $sub_account_name = "";
            $tax_category = "";
            $otherlineCount = AccountJournalLine::where('journal_entry_id', $line->journal_entry_id)->where('id','!=',$line->id)->count();
            if ($otherlineCount == 1) {
                $otherline = AccountJournalLine::where('journal_entry_id', $line->journal_entry_id)->where('id','!=',$line->id)->first();
                $account_name = $otherline->account->name ?? '';
                $sub_account_name = $otherline->subAccount->name ?? '';
                $tax_category = $otherline->taxType->name ?? '';
                
            }
            if($line->side ==2 ){
                $jie_money = "";
                $dai_money = $line->amount;

            }else{
                $jie_money = $line->amount;
                $dai_money = "";
            }

            $datas['rows'][] = [
                'date' => $line->entry->posting_date->format('Y-m-d'),
                'account_name' => $account_name,
                'sub_account_name' => $sub_account_name,
                'tax_category' => $tax_category,
                'jie_money' => $jie_money,
                'dai_money' => $dai_money,
            ];
        }

         return response()->json($datas);

    }

}