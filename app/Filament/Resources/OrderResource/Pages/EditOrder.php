<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Notifications\OrderConfirmedNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $oldStatus = null;

    protected function beforeFill(): void
    {
        $this->oldStatus = $this->record->status;
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $status = $data['status'] ?? null;
        $shippingPerItem = isset($data['shipping_per_item'])
            ? (float) $data['shipping_per_item']
            : 0.0;

        if ($status === 'confirmed' && $shippingPerItem <= 0) {
            Notification::make()
                ->title('Shipping fee is required')
                ->body('You must enter shipping per item before confirming the order.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $this->record->refresh();

        if (
            $this->record->status === 'confirmed' &&
            $this->record->shipping_per_item > 0 &&
            $this->record->user &&
            $this->oldStatus !== 'confirmed'
        ) {
            $this->record->user->notify(
                new OrderConfirmedNotification($this->record)
            );
        }
    }
}