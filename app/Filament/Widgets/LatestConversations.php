<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestConversations extends BaseWidget
{
    protected static ?string $heading = 'Latest Conversations';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Conversation::query()
                    ->with(['user', 'latestMessage'])
                    ->withCount([
                        'messages as unread_count' => function (Builder $query) {
                            $query->where('is_seen', false)
                                ->whereColumn('messages.sender_id', 'conversations.user_id');
                        },
                    ])
                    ->latest('last_message_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->limit(30),

                Tables\Columns\TextColumn::make('latestMessage.message')
                    ->label('Last Message')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('unread_count')
                    ->label('New')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->url(fn (Conversation $record): string => route('filament.admin.resources.conversations.edit', ['record' => $record])),
            ])
            ->paginated([5]);
    }
}