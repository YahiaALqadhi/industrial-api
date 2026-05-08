<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Orders';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Info')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('currency')
                            ->default('USD')
                            ->required()
                            ->maxLength(10),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Receipt')
                    ->schema([
                        Forms\Components\Placeholder::make('payment_receipt_preview')
                            ->label('Receipt')
                            ->content(function (?Order $record) {
                                if (! $record || blank($record->payment_receipt)) {
                                    return new HtmlString('<span style="color:#6b7280;">No receipt uploaded yet.</span>');
                                }

                                $url = asset('storage/' . $record->payment_receipt);

                                return new HtmlString(
                                    '<a href="' . $url . '" target="_blank">
                                        <img src="' . $url . '" style="max-width:320px; max-height:260px; object-fit:contain; border-radius:12px; border:1px solid #e5e7eb; background:white; padding:8px;">
                                    </a>'
                                );
                            }),

                        Forms\Components\TextInput::make('payment_receipt')
                            ->label('Receipt Path')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('customer_phone')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('shipping_address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Calculated automatically from order items.'),

                        Forms\Components\TextInput::make('shipping_per_item')
                            ->label('Shipping per item')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state, $record) {
                                $shippingPerItem = is_numeric($state) ? (float) $state : 0;
                                $totalQty = $record?->items()->sum('quantity') ?? 0;
                                $subtotal = (float) ($record?->subtotal ?? 0);
                                $discount = is_numeric($get('discount_amount')) ? (float) $get('discount_amount') : 0;

                                $shippingCost = $shippingPerItem * $totalQty;
                                $totalAmount = $subtotal + $shippingCost - $discount;

                                $set('shipping_cost', $shippingCost);
                                $set('total_amount', max($totalAmount, 0));
                            })
                            ->helperText('This value will be multiplied by total item quantity.'),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Calculated automatically from shipping per item × total quantity.'),

                        Forms\Components\TextInput::make('discount_amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state, $record) {
                                $discount = is_numeric($state) ? (float) $state : 0;
                                $shippingPerItem = is_numeric($get('shipping_per_item')) ? (float) $get('shipping_per_item') : 0;
                                $totalQty = $record?->items()->sum('quantity') ?? 0;
                                $subtotal = (float) ($record?->subtotal ?? 0);

                                $shippingCost = $shippingPerItem * $totalQty;
                                $totalAmount = $subtotal + $shippingCost - $discount;

                                $set('shipping_cost', $shippingCost);
                                $set('total_amount', max($totalAmount, 0));
                            }),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Calculated automatically: subtotal + shipping - discount.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ordered Products')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('product_name')
                                    ->required()
                                    ->disabled(),

                                Forms\Components\TextInput::make('product_sku')
                                    ->label('SKU')
                                    ->disabled(),

                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled(),

                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('total_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->money('USD')
                    ->label('Shipping')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('payment_receipt')
                    ->label('Receipt')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('ordered_at')
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
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}