<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Order confirmed',
            'body' => "Your order {$this->order->order_number} has been reviewed. Shipping cost was added and the final total is ready for payment.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'shipping_cost' => $this->order->shipping_cost,
            'total_amount' => $this->order->total_amount,
            'type' => 'order_confirmed',
        ];
    }
}