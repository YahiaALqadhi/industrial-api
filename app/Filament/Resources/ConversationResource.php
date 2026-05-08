<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $modelLabel = 'Conversation';

    protected static ?string $pluralModelLabel = 'Conversations';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->whereHas('messages', function (Builder $query) {
                $query->where('is_seen', false)
                    ->whereColumn('sender_id', 'conversations.user_id');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversation Info')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('user.email')
                            ->label('Customer Email')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('subject')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'pending' => 'Pending',
                                'closed' => 'Closed',
                            ])
                            ->required(),

                        Forms\Components\Select::make('admin_id')
                            ->label('Assigned Admin')
                            ->relationship('admin', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('last_message_at')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['latestMessage', 'user', 'admin'])
                ->withCount([
                    'messages as unread_count' => function (Builder $query) {
                        $query->where('is_seen', false)
                            ->whereColumn('sender_id', 'conversations.user_id');
                    },
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
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
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Assigned Admin')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'pending' => 'Pending',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\TernaryFilter::make('has_unread')
                    ->label('Has New Messages')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('messages', function (Builder $q) {
                            $q->where('is_seen', false)
                                ->whereColumn('sender_id', 'conversations.user_id');
                        }),
                        false: fn (Builder $query) => $query->whereDoesntHave('messages', function (Builder $q) {
                            $q->where('is_seen', false)
                                ->whereColumn('sender_id', 'conversations.user_id');
                        }),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Open Chat'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}