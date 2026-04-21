<?php

namespace App\Events;

use App\Models\Sales\Sale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when compensation_received is marked true on a sale. */
class SaleCompensated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Sale $sale) {}
}
