<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $slip->employee->display_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111827; }

        /* ── Header ── */
        .header { background: #111827; color: #ffffff; padding: 18px 28px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .header-company { font-size: 15px; font-weight: 700; color: #ffffff; }
        .header-sub { font-size: 10px; color: #9ca3af; margin-top: 4px; }
        .header-period { font-size: 11px; color: #d1d5db; text-align: right; }
        .draft-badge { display: inline-block; background: #78350f; color: #fde68a; font-size: 9px; padding: 2px 6px; border-radius: 3px; margin-top: 4px; }

        /* ── Employee grid ── */
        .emp-section { padding: 14px 28px; border-bottom: 1px solid #e5e7eb; }
        .emp-section table { width: 100%; border-collapse: collapse; }
        .emp-section td { width: 25%; vertical-align: top; padding-right: 10px; }
        .emp-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
        .emp-value { font-weight: 600; font-size: 11px; color: #111827; }

        /* ── Sections ── */
        .section { padding: 12px 28px; border-bottom: 1px solid #f3f4f6; }
        .section-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 8px; }
        .line { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .line td { padding: 1px 0; }
        .line .lbl { color: #374151; }
        .line .val { text-align: right; color: #111827; }
        .line .val-green { text-align: right; color: #15803d; }
        .line .val-red { text-align: right; color: #dc2626; }

        /* ── Net Pay ── */
        .net-section { padding: 12px 28px; background: #eef2ff; border-bottom: 1px solid #e5e7eb; }
        .net-section table { width: 100%; border-collapse: collapse; }
        .net-label { font-weight: 700; font-size: 13px; color: #111827; }
        .net-value { font-weight: 700; font-size: 13px; color: #4338ca; text-align: right; }

        /* ── YTD ── */
        .ytd-section { padding: 12px 28px; border-bottom: 1px solid #f3f4f6; }
        .ytd-section table { width: 100%; border-collapse: collapse; }
        .ytd-section td { width: 50%; vertical-align: top; padding-right: 10px; }
        .ytd-section td:last-child { padding-right: 0; }
        .ytd-total { border-top: 1px solid #e5e7eb; margin-top: 4px; padding-top: 4px; font-weight: 700; }

        /* ── Footer ── */
        .footer { padding: 10px 28px; background: #f9fafb; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <table>
        <tr>
            <td>
                <div class="header-company">{{ $settings->company_name }}</div>
                <div class="header-sub">Payslip &mdash; {{ $slip->payrollRun->payPeriod->label }}</div>
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <div class="header-period">
                    {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->start_date)->format('M j') }}
                    &ndash; {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->format('M j, Y') }}
                </div>
                @if($slip->payrollRun->status === 'draft')
                <div class="draft-badge">DRAFT</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- Employee Info --}}
<div class="emp-section">
    <table>
        <tr>
            <td>
                <div class="emp-label">Employee</div>
                <div class="emp-value">{{ $slip->employee->display_name }}</div>
            </td>
            <td>
                <div class="emp-label">Code</div>
                <div class="emp-value">{{ $slip->employee->employee_code }}</div>
            </td>
            <td>
                <div class="emp-label">Job Title</div>
                <div class="emp-value">{{ $slip->employee->jobTitle->title ?? '—' }}</div>
            </td>
            <td>
                <div class="emp-label">Department</div>
                <div class="emp-value">{{ $slip->employee->department->name ?? '—' }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Earnings --}}
<div class="section">
    <div class="section-title">Earnings</div>

    @php
        $hoursLabel = '';
        if ($slip->regular_hours > 0 || $slip->overtime_hours > 0) {
            $hoursLabel = ' (' . number_format($slip->regular_hours, 2) . 'h regular';
            if ($slip->overtime_hours > 0) {
                $hoursLabel .= ', ' . number_format($slip->overtime_hours, 2) . 'h OT';
            }
            $hoursLabel .= ')';
        }
    @endphp
    <table class="line"><tr>
        <td class="lbl">Base / Gross Salary{{ $hoursLabel }}</td>
        <td class="val">{{ number_format($slip->gross_salary, 2) }}</td>
    </tr></table>

    @if($slip->commission_earned > 0)
    <table class="line"><tr>
        <td class="lbl">Commission</td>
        <td class="val">{{ number_format($slip->commission_earned, 2) }}</td>
    </tr></table>
    @endif

    @foreach($slip->lineItems->where('type', 'addition') as $item)
    <table class="line"><tr>
        <td class="lbl">{{ $item->description }}</td>
        <td class="val-green">{{ number_format($item->amount, 2) }}</td>
    </tr></table>
    @endforeach
</div>

{{-- Deductions & Taxes --}}
@if($slip->lineItems->whereIn('type', ['deduction', 'tax'])->isNotEmpty())
<div class="section">
    <div class="section-title">Deductions &amp; Taxes</div>
    @foreach($slip->lineItems->where('type', 'deduction') as $item)
    <table class="line"><tr>
        <td class="lbl">{{ $item->description }}</td>
        <td class="val-red">({{ number_format($item->amount, 2) }})</td>
    </tr></table>
    @endforeach
    @foreach($slip->lineItems->where('type', 'tax') as $item)
    <table class="line"><tr>
        <td class="lbl">{{ $item->description }}</td>
        <td class="val-red">({{ number_format($item->amount, 2) }})</td>
    </tr></table>
    @endforeach
</div>
@endif

{{-- Net Pay --}}
<div class="net-section">
    <table>
        <tr>
            <td class="net-label">NET PAY</td>
            <td class="net-value">{{ number_format($slip->net_pay, 2) }}</td>
        </tr>
    </table>
</div>

{{-- Year-to-Date --}}
@php
    $ytd = $slip->ytd();
    $ytdYear = \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->year;
    $ytdNote = $slip->payrollRun->status !== 'finalized' ? ' — excludes this draft period' : '';
@endphp
<div class="ytd-section">
    <div class="section-title">Year-to-Date ({{ $ytdYear }}){{ $ytdNote }}</div>
    <table>
        <tr>
            <td>
                <table class="line"><tr><td class="lbl">Gross Salary</td><td class="val">{{ number_format($ytd->gross, 2) }}</td></tr></table>
                @if($ytd->commission > 0)
                <table class="line"><tr><td class="lbl">Commission</td><td class="val">{{ number_format($ytd->commission, 2) }}</td></tr></table>
                @endif
                @if($ytd->additions > 0)
                <table class="line"><tr><td class="lbl">Additions</td><td class="val-green">{{ number_format($ytd->additions, 2) }}</td></tr></table>
                @endif
            </td>
            <td>
                <table class="line"><tr><td class="lbl">Deductions</td><td class="val-red">({{ number_format($ytd->deductions, 2) }})</td></tr></table>
                <table class="line"><tr><td class="lbl">Tax Withheld</td><td class="val-red">({{ number_format($ytd->tax, 2) }})</td></tr></table>
                <table class="line ytd-total"><tr>
                    <td class="lbl">Net Pay YTD</td>
                    <td style="text-align:right; color:#4338ca; font-weight:700;">{{ number_format($ytd->net, 2) }}</td>
                </tr></table>
            </td>
        </tr>
    </table>
</div>

{{-- Footer --}}
<div class="footer">
    {{ $settings->company_name }} &middot; Computer-generated payslip &middot; {{ now()->format('F j, Y g:i A') }}
</div>

</body>
</html>
