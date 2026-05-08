<?php

namespace App\Notifications;

use App\Models\Order;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
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
        return FilamentNotification::make()
            ->title('New order received')
            ->body("Order {$this->order->order_number} has been created.")
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Order')
                    ->url(route('filament.admin.resources.orders.edit', ['record' => $this->order])),
            ])
            ->getDatabaseMessage();
    }
}