<?php

namespace App\Events;

use App\Models\Attendance\AttendanceLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceLogged
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly AttendanceLog $log) {}
}
