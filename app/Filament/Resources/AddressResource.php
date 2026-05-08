<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Addresses';

    protected static ?string $modelLabel = 'Address';

    protected static ?string $pluralModelLabel = 'Addresses';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(User::pluck('email', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('label')
                    ->label('Label')
                    ->placeholder('Home / Office / Warehouse')
                    ->maxLength(255),

                Forms\Components\TextInput::make('recipient_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->maxLength(255),

                Forms\Components\TextInput::make('country')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->maxLength(255),

                Forms\Components\TextInput::make('postal_code')
                    ->maxLength(255),

                Forms\Components\Textarea::make('address_line_1')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('address_line_2')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_default')
                    ->label('Default Address')
                    ->default(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Address'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}