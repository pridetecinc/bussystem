<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // 簡易検索（名前やコードで絞り込み）
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_code', 'like', "%{$s}%");
        }

        $customers = $query->orderBy('customer_code')->paginate(20);
        return view('masters.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('masters.customers.edit', ['customer' => new Customer()]);
    }

    public function edit(Customer $customer)
    {
        return view('masters.customers.edit', compact('customer'));
    }

    public function store(Request $request)
    {
        Customer::create($request->all());
        return redirect()->route('customers.index')->with('success', '顧客を登録しました。');
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', '顧客情報を更新しました。');
    }
}