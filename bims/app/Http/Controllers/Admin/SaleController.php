<?php

namespace App\Http\Controllers\Admin;

use App\Events\SaleCompensated;
use App\Events\SaleRecorded;
use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Team;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleFieldDefinition;
use App\Models\Sales\SaleType;
use App\Models\User;
use App\Notifications\SaleStatusChanged;
use App\Services\Sales\CommissionCalculator;
use App\Services\Sales\FormulaEvaluator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private CommissionCalculator $commission,
        private FormulaEvaluator $formulaEvaluator,
    ) {}

    public function index(): View
    {
        $sales = $this->scopeSales(Sale::with(['employee', 'saleType']))
            ->orderByDesc('sale_date')
            ->paginate(25);

        $tableFields = SaleFieldDefinition::where('is_active', true)
            ->where('show_in_table', true)
            ->orderBy('sort_order')->orderBy('label')
            ->get();

        return view('admin.sales.index', compact('sales', 'tableFields'));
    }

    public function filter(Request $request): View
    {
        $query = $this->scopeSales(Sale::with(['employee', 'saleType']))->orderByDesc('sale_date');

        if ($emp = $request->input('employee_id')) {
            $query->where('employee_id', $emp);
        }
        if ($type = $request->input('sale_type_id')) {
            $query->where('sale_type_id', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('sale_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('sale_date', '<=', $to);
        }
        if ($request->input('uncompensated')) {
            $query->where('compensation_received', false)->where('status', 'Approved');
        }
        // Team filter: only honoured if user can already see that team
        if ($teamId = $request->input('team_id')) {
            $query->where('team_id', $teamId);
        }

        $user      = auth()->user();
        $teamId    = $user->scopedTeamId();
        $sales     = $query->paginate(25)->withQueryString();
        $employees = $teamId
            ? Employee::active()->where('team_id', $teamId)->orderBy('lastname')->get()
            : Employee::active()->orderBy('lastname')->get();
        $saleTypes = SaleType::where('is_active', true)->orderBy('product_category')->get();
        $teams     = $user->isAdmin() ? Team::active()->orderBy('name')->get() : collect();

        return view('admin.sales.filter', compact('sales', 'employees', 'saleTypes', 'teams'));
    }

    /**
     * Applies the correct visibility scope based on the authenticated user's permissions:
     *   - Admin or view_all → no filter
     *   - view_team         → scoped to their team (or own if no team assigned)
     *   - default           → own sales only
     */
    private function scopeSales(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->hasPermission('sales', 'view_all')) {
            return $query;
        }

        if ($user->hasPermission('sales', 'view_team')) {
            $teamId = $user->scopedTeamId();
            return $teamId
                ? $query->where('team_id', $teamId)
                : $query->where('employee_id', $user->employee_id);
        }

        return $query->where('employee_id', $user->employee_id);
    }

    public function create(): View
    {
        $fields = SaleFieldDefinition::where('is_active', true)
            ->where('show_on_create', true)
            ->orderBy('sort_order')->orderBy('label')->get();

        return view('admin.sales.create', [
            'employees'        => Employee::active()->orderBy('lastname')->get(),
            'saleTypes'        => SaleType::where('is_active', true)->orderBy('product_category')->get(),
            'fieldsBySaleType' => $fields->groupBy(fn($f) => $f->sale_type_id ?? 'all')->toBase(),
            'builtinKeys'      => FormulaEvaluator::builtinKeys(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id'   => ['required', 'exists:employees,id'],
            'sale_type_id'  => ['required', 'exists:sale_types,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone'=> ['nullable', 'string', 'max:30'],
            'sale_date'     => ['required', 'date'],
            'status'        => ['required', 'in:Submitted,Approved,Cancelled,Pending Cancellation'],
        ]);

        $points = $this->commission->calculate($validated['employee_id'], $validated['sale_type_id']);

        $metadata = $this->extractMetadata($request, $validated['sale_type_id']);

        $employee = Employee::find($validated['employee_id']);

        $sale = Sale::create([
            'employee_id'           => $validated['employee_id'],
            'team_id'               => $employee?->team_id,
            'sale_type_id'          => $validated['sale_type_id'],
            'customer_name'         => $validated['customer_name'],
            'customer_phone'        => $validated['customer_phone'] ?? null,
            'sale_date'             => $validated['sale_date'],
            'status'                => $validated['status'],
            'total_points'          => $points['total_points'],
            'agent_points'          => $points['agent_points'],
            'compensation_received' => false,
            'metadata'              => $metadata ?: null,
        ]);

        event(new SaleRecorded($sale));

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale created.');
    }

    public function edit(Sale $sale): View
    {
        $sale->load(['employee', 'saleType']);
        return view('admin.sales.edit', [
            'sale'      => $sale,
            'saleTypes' => SaleType::where('is_active', true)->orderBy('product_category')->get(),
            'statuses'  => ['Submitted', 'Approved', 'Cancelled', 'Pending Cancellation'],
            'customFields' => SaleFieldDefinition::forSaleType($sale->sale_type_id),
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'  => ['required', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'sale_date'      => ['required', 'date'],
            'status'         => ['required', 'in:Submitted,Approved,Cancelled,Pending Cancellation'],
        ]);

        $oldStatus = $sale->status;
        $metadata  = $this->extractMetadata($request, $sale->sale_type_id, $sale);

        $sale->update($validated + ['metadata' => $metadata ?: null]);

        if ($sale->status !== $oldStatus) {
            $sale->load('saleType');
            $user = User::where('employee_id', $sale->employee_id)->first();
            $user?->notify(new SaleStatusChanged($sale, $oldStatus));
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale updated.');
    }

    private function extractMetadata(Request $request, ?int $saleTypeId, ?Sale $sale = null): array
    {
        $fields = SaleFieldDefinition::forSaleType($saleTypeId);
        $meta   = [];

        // Pass 1: collect regular user-entered values
        foreach ($fields->where('field_type', '!=', 'calculated') as $field) {
            $val = $request->input("meta_{$field->key}");
            if ($val !== null && $val !== '') {
                $meta[$field->key] = $field->field_type === 'checkbox' ? (bool) $val : $val;
            }
        }

        // Pass 2: evaluate calculated fields (they can reference pass-1 values)
        foreach ($fields->where('field_type', 'calculated') as $field) {
            if (! $field->formula) continue;
            // Use a temporary Sale instance to expose built-in columns
            $context = $sale ?? new Sale($request->only(
                'employee_id', 'sale_type_id', 'sale_date', 'status',
                'total_points', 'agent_points', 'customer_name', 'customer_phone'
            ));
            $meta[$field->key] = $this->formulaEvaluator->evaluate($field->formula, $context, $meta);
        }

        return $meta;
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();
        return back()->with('success', 'Sale deleted.');
    }

    public function markCompensated(Sale $sale): RedirectResponse
    {
        if ($sale->compensation_received) {
            return back()->with('error', 'Already marked as compensated.');
        }

        $sale->update(['compensation_received' => true]);
        event(new SaleCompensated($sale));

        return back()->with('success', 'Sale marked as compensated. Commission will be included in next payroll.');
    }
}
