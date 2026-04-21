<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $slip->employee->display_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111827; padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .logo { font-size: 20px; font-weight: 700; }
        .logo span { color: #4f46e5; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; }
        .row.bold { font-weight: 700; font-size: 13px; }
        .label { color: #374151; }
        .value { color: #111827; }
        .deduction { color: #dc2626; }
        .net-pay { background: #eef2ff; padding: 12px 16px; border-radius: 6px; margin-top: 16px; }
        .grid-2 { display: flex; gap: 40px; margin-bottom: 16px; }
        .grid-2 > div { flex: 1; }
        .text-small { font-size: 10px; color: #6b7280; }
        .footer { margin-top: 32px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo"><span>B</span>IMS &mdash; Payslip</div>
            <div class="text-small" style="margin-top:4px;">Beroni Innovations Management System</div>
        </div>
        <div style="text-align:right;">
            <div style="font-weight:600;">{{ $slip->payrollRun->payPeriod->label }}</div>
            <div class="text-small">
                {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->start_date)->format('M j') }}
                – {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->format('M j, Y') }}
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <div class="text-small">Employee</div>
            <div style="font-weight:600;">{{ $slip->employee->display_name }}</div>
        </div>
        <div>
            <div class="text-small">Employee Code</div>
            <div>{{ $slip->employee->employee_code }}</div>
        </div>
        <div>
            <div class="text-small">Job Title</div>
            <div>{{ $slip->employee->jobTitle->title ?? '—' }}</div>
        </div>
        <div>
            <div class="text-small">Department</div>
            <div>{{ $slip->employee->department->name ?? '—' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Earnings</div>
        <div class="row">
            <span class="label">Gross Salary ({{ number_format($slip->regular_hours, 2) }}h regular, {{ number_format($slip->overtime_hours, 2) }}h OT)</span>
            <span class="value">{{ number_format($slip->gross_salary, 2) }}</span>
        </div>
        @if($slip->commission_earned > 0)
        <div class="row">
            <span class="label">Commission</span>
            <span class="value">{{ number_format($slip->commission_earned, 2) }}</span>
        </div>
        @endif
        @foreach($slip->lineItems->where('type', 'addition') as $item)
        <div class="row">
            <span class="label">{{ $item->description }}</span>
            <span class="value" style="color:#15803d;">{{ number_format($item->amount, 2) }}</span>
        </div>
        @endforeach
    </div>

    @if($slip->lineItems->whereIn('type', ['deduction', 'tax'])->isNotEmpty())
    <div class="section">
        <div class="section-title">Deductions & Taxes</div>
        @foreach($slip->lineItems->where('type', 'deduction') as $item)
        <div class="row">
            <span class="label">{{ $item->description }}</span>
            <span class="deduction">({{ number_format($item->amount, 2) }})</span>
        </div>
        @endforeach
        @foreach($slip->lineItems->where('type', 'tax') as $item)
        <div class="row">
            <span class="label">{{ $item->description }}</span>
            <span class="deduction">({{ number_format($item->amount, 2) }})</span>
        </div>
        @endforeach
    </div>
    @endif

    <div class="net-pay">
        <div class="row bold">
            <span>NET PAY</span>
            <span style="color:#4338ca;">{{ number_format($slip->net_pay, 2) }}</span>
        </div>
    </div>

    @if($slip->payrollRun->status === 'finalized')
    @php $ytd = $slip->ytd(); @endphp
    <div class="section" style="margin-top:20px;">
        <div class="section-title">Year-to-Date ({{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->year }})</div>
        <div style="display:flex;gap:40px;">
            <div style="flex:1;">
                <div class="row"><span class="label">Gross Salary</span><span class="value">{{ number_format($ytd->gross, 2) }}</span></div>
                @if($ytd->commission > 0)
                <div class="row"><span class="label">Commission</span><span class="value">{{ number_format($ytd->commission, 2) }}</span></div>
                @endif
                @if($ytd->additions > 0)
                <div class="row"><span class="label">Additions</span><span class="value" style="color:#15803d;">{{ number_format($ytd->additions, 2) }}</span></div>
                @endif
            </div>
            <div style="flex:1;">
                <div class="row"><span class="label">Deductions</span><span class="deduction">({{ number_format($ytd->deductions, 2) }})</span></div>
                <div class="row"><span class="label">Tax Withheld</span><span class="deduction">({{ number_format($ytd->tax, 2) }})</span></div>
                <div class="row bold" style="border-top:1px solid #e5e7eb;margin-top:4px;padding-top:4px;">
                    <span>Net Pay YTD</span><span style="color:#4338ca;">{{ number_format($ytd->net, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        This is a computer-generated payslip. Generated by BIMS on {{ now()->format('F j, Y g:i A') }}.
    </div>
</body>
</html>
