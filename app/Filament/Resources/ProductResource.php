<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(Category::where('is_active', true)->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),

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

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('brand')
                    ->maxLength(255),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $price = is_numeric($state) ? (float) $state : 0;
                        $discountValue = is_numeric($get('discount_value')) ? (float) $get('discount_value') : 0;

                        $discountedPrice = max($price - $discountValue, 0);
                        $set('discount_price', $discountValue > 0 ? $discountedPrice : null);
                    }),

                Forms\Components\TextInput::make('discount_value')
                    ->label('Discount Value')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->dehydrated(false)
                    ->formatStateUsing(function ($record) {
                        if (! $record) {
                            return 0;
                        }

                        $price = (float) $record->price;
                        $discountPrice = $record->discount_price !== null ? (float) $record->discount_price : null;

                        if ($discountPrice === null || $discountPrice >= $price) {
                            return 0;
                        }

                        return $price - $discountPrice;
                    })
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $price = is_numeric($get('price')) ? (float) $get('price') : 0;
                        $discountValue = is_numeric($state) ? (float) $state : 0;

                        $discountedPrice = max($price - $discountValue, 0);
                        $set('discount_price', $discountValue > 0 ? $discountedPrice : null);
                    })
                    ->helperText('Enter the discount amount, not the final price.'),

                Forms\Components\TextInput::make('discount_price')
                    ->label('Final Price After Discount')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Calculated automatically from Price - Discount Value.'),

                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Section::make('Main Product Image')
                    ->schema([
                        Forms\Components\Placeholder::make('current_main_image')
                            ->label('Current Image')
                            ->content(function ($record) {
                                if (! $record || ! $record->main_image) {
                                    return new HtmlString(
                                        '<div style="color:#6b7280;">No product image uploaded.</div>'
                                    );
                                }

                                return new HtmlString(
                                    '<img src="' . asset('storage/' . $record->main_image) . '" alt="Product Image" style="width:120px;height:120px;border-radius:16px;object-fit:cover;border:1px solid #e5e7eb;">'
                                );
                            }),

                        Forms\Components\FileUpload::make('main_image')
                            ->label('Upload New Image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
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

                Forms\Components\Section::make('Product Gallery')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Gallery Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('products/gallery')
                                    ->visibility('public')
                                    ->required()
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->downloadable()
                                    ->openable()
                                    ->previewable(false)
                                    ->deleteUploadedFileUsing(function (string $file) {
                                        Storage::disk('public')->delete($file);
                                    }),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->collapsed()
                            ->reorderable()
                            ->addActionLabel('Add Image')
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('short_description')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured')
                    ->default(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->main_image ? asset('storage/' . $record->main_image) : null),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_price')
                    ->label('Final Price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock')
                    ->sortable(),

                Tables\Columns\TextColumn::make('images_count')
                    ->label('Gallery')
                    ->counts('images')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}