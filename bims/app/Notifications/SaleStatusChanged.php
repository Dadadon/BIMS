<?php

namespace App\Notifications;

use App\Models\Sales\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SaleStatusChanged extends Notification
{
    use Queueable;

    public function __construct(private Sale $sale, private string $oldStatus) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type   = $this->sale->saleType?->product_category ?? 'Sale';
        $name   = $this->sale->customer_name;
        $status = $this->sale->status;

        return [
            'icon'       => 'sale',
            'message'    => "Your sale for {$name} ({$type}) has been changed to {$status}.",
            'sale_id'    => $this->sale->id,
            'old_status' => $this->oldStatus,
            'new_status' => $status,
            'url'        => null,
        ];
    }
}
