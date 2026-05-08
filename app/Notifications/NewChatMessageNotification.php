<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Conversation $conversation,
        public Message $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $customerName = $this->conversation->user?->name ?? 'Customer';

        return FilamentNotification::make()
            ->title('New chat message')
            ->body("{$customerName} sent a new message.")
            ->icon('heroicon-o-chat-bubble-left-right')
            ->iconColor('warning')
            ->actions([
                Action::make('open')
                    ->label('Open Chat')
                    ->url(route('filament.admin.resources.conversations.edit', ['record' => $this->conversation])),
            ])
            ->getDatabaseMessage();
    }
}