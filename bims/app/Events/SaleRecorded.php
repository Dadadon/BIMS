<?php

namespace App\Events;

use App\Models\Sales\Sale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired immediately when a sale is created. Drives performance KPIs only. */
class SaleRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Sale $sale) {}
}
