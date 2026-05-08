<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Categories';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(Category::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Section::make('Category Image')
                    ->schema([
                        Forms\Components\Placeholder::make('current_image')
                            ->label('Current Image')
                            ->content(function ($record) {
                                if (! $record || ! $record->image) {
                                    return new HtmlString(
                                        '<div style="color:#6b7280;">No category image uploaded.</div>'
                                    );
                                }

                                return new HtmlString(
                                    '<img src="' . asset('storage/' . $record->image) . '" alt="Category Image" style="width:120px;height:120px;border-radius:16px;object-fit:cover;border:1px solid #e5e7eb;">'
                                );
                            }),

                        Forms\Components\FileUpload::make('image')
                            ->label('Upload New Image')
                            ->image()
                            ->disk('public')
                            ->directory('categories')
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
                    ->columnSpan(1),

                Forms\Components\Textarea::make('description')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->image ? asset('storage/' . $record->image) : null),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}