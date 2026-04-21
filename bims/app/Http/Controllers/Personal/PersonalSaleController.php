<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Sales\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalSaleController extends Controller
{
    public function index(Request $request): View
    {
        $employee = auth()->user()->employee;

        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $sales = collect();
        if ($employee) {
            $sales = Sale::where('employee_id', $employee->id)
                ->with('saleType')
                ->whereDate('sale_date', '>=', $month->copy()->startOfMonth())
                ->whereDate('sale_date', '<=', $month->copy()->endOfMonth())
                ->orderByDesc('sale_date')
                ->paginate(20);
        }

        return view('personal.sales', compact('employee', 'sales', 'month'));
    }
}
