<?php

namespace App\Notifications;

use App\Models\Payroll\PayrollSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayrollFinalized extends Notification
{
    use Queueable;

    public function __construct(private PayrollSlip $slip) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label  = $this->slip->payrollRun?->payPeriod?->label ?? 'pay period';
        $net    = number_format($this->slip->net_pay, 2);

        return [
            'icon'     => 'payroll',
            'message'  => "Your payslip for {$label} is ready. Net pay: ₱{$net}.",
            'slip_id'  => $this->slip->id,
            'run_id'   => $this->slip->payroll_run_id,
            'url'      => null,
        ];
    }
}
