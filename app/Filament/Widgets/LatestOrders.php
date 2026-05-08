<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge(),

                Tables\Columns\TextColumn::make('ordered_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Open')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', ['record' => $record])),
            ])
            ->paginated([5]);
    }
}