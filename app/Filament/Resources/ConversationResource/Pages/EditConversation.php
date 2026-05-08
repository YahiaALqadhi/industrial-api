<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Models\Message;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditConversation extends EditRecord
{
    protected static string $resource = ConversationResource::class;

    protected static string $view = 'filament.resources.conversation-resource.pages.edit-conversation';

    public ?array $replyData = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->messages()
            ->where('sender_id', $this->record->user_id)
            ->where('is_seen', false)
            ->update([
                'is_seen' => true,
                'seen_at' => now(),
            ]);

        $this->record->refresh();
    }

    protected function getForms(): array
    {
        return [
            'form',
            'replyForm',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function replyForm(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('Reply Message')
                    ->required()
                    ->rows(4),
            ])
            ->statePath('replyData');
    }

    public function sendReply(): void
    {
        $data = $this->replyForm->getState();

        if (blank($data['message'] ?? null)) {
            Notification::make()
                ->title('Reply message is required.')
                ->danger()
                ->send();

            return;
        }

        Message::create([
            'conversation_id' => $this->record->id,
            'sender_id' => auth()->id(),
            'message' => $data['message'],
            'is_seen' => false,
        ]);

        $this->record->update([
            'admin_id' => $this->record->admin_id ?: auth()->id(),
            'status' => 'pending',
            'last_message_at' => now(),
        ]);

        $this->replyData = [];

        Notification::make()
            ->title('Reply sent successfully.')
            ->success()
            ->send();

        $this->record->refresh();
    }
}