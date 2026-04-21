<?php

namespace App\Notifications;

use App\Models\Leave\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(private LeaveRequest $leave) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->leave->status;
        $type   = $this->leave->leaveType?->name ?? 'Leave';
        $from   = $this->leave->date_from?->format('M j');
        $to     = $this->leave->date_to?->format('M j, Y');

        return [
            'icon'     => 'leave',
            'message'  => "Your {$type} request ({$from}–{$to}) has been {$status}.",
            'status'   => $status,
            'leave_id' => $this->leave->id,
            'url'      => null,
        ];
    }
}
