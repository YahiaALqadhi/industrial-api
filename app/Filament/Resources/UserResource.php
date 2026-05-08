<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Image')
                    ->schema([
                        Forms\Components\Placeholder::make('current_avatar')
                            ->label('Current Image')
                            ->content(function ($record) {
                                if (! $record || ! $record->avatar) {
                                    return new HtmlString(
                                        '<div style="color:#6b7280;">No profile image uploaded.</div>'
                                    );
                                }

                                return new HtmlString(
                                    '<img src="' . asset('storage/' . $record->avatar) . '" alt="Profile Image" style="width:110px;height:110px;border-radius:9999px;object-fit:cover;border:1px solid #e5e7eb;">'
                                );
                            }),

                        Forms\Components\FileUpload::make('avatar')
                            ->label('Upload New Image')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->nullable()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->deleteUploadedFileUsing(function (string $file) {
                                Storage::disk('public')->delete($file);
                            })
                            ->helperText('You can upload a new image to replace the current one, or remove it.'),
                    ])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null),

                Forms\Components\Select::make('role')
                    ->label('Role')
                    ->options(Role::pluck('name', 'name')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default('admin')
                    ->afterStateHydrated(function ($state, $record, $component) {
                        if ($record) {
                            $component->state($record->getRoleNames()->first());
                        }
                    })
                    ->dehydrated(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->avatar ? asset('storage/' . $record->avatar) : null),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => ! $record->hasRole('super_admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records
                                ->reject(fn (User $user) => $user->hasRole('super_admin'))
                                ->each
                                ->delete();
                        }),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}