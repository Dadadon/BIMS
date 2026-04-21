<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report\SavedReport;
use App\Services\Report\ReportBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportBuilderService $builder) {}

    public function index(): View
    {
        $reports = SavedReport::with('creator')->latest()->get();
        return view('admin.reports.index', compact('reports'));
    }

    public function create(): View
    {
        $schema = $this->builder->schemaForFrontend();
        return view('admin.reports.create', compact('schema'));
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $this->validateReport($request);
        $v['created_by'] = auth()->id();

        $report = SavedReport::create($v);
        return redirect()->route('admin.reports.show', $report)->with('success', 'Report saved.');
    }

    public function show(SavedReport $report): View
    {
        $result = $this->builder->run($report);
        $schema = $this->builder->schemaForFrontend();

        return view('admin.reports.show', compact('report', 'result', 'schema'));
    }

    public function edit(SavedReport $report): View
    {
        $schema = $this->builder->schemaForFrontend();
        return view('admin.reports.create', compact('report', 'schema'));
    }

    public function update(Request $request, SavedReport $report): RedirectResponse
    {
        $v = $this->validateReport($request);
        $report->update($v);
        return redirect()->route('admin.reports.show', $report)->with('success', 'Report updated.');
    }

    public function destroy(SavedReport $report): RedirectResponse
    {
        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted.');
    }

    public function export(SavedReport $report): StreamedResponse
    {
        $result = $this->builder->run($report, limit: 10000);
        $columns = $result['columns'];
        $rows    = $result['rows'];

        $filename = str($report->name)->slug() . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_column($columns, 'label'));
            foreach ($rows as $row) {
                $row = (array) $row;
                fputcsv($handle, array_map(fn($col) => $row[$col['key']] ?? '', $columns));
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:500'],
            'data_source'      => ['required', 'string'],
            'columns'          => ['required', 'array', 'min:1'],
            'columns.*'        => ['string'],
            'filters'          => ['nullable', 'array'],
            'filters.*.field'  => ['required_with:filters', 'string'],
            'filters.*.op'     => ['required_with:filters', 'string'],
            'filters.*.value'  => ['required_with:filters', 'string'],
            'group_by'         => ['nullable', 'string'],
            'aggregate_field'  => ['nullable', 'string'],
            'chart_type'       => ['required', 'string', 'in:table,bar,line,area,pie,doughnut'],
        ]);
    }
}
