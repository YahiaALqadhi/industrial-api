<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders in Last 7 Days';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo);
        });

        $labels = $days->map(fn ($date) => $date->format('D'))->toArray();

        $data = $days->map(function ($date) {
            return Order::query()
                ->whereDate('created_at', $date->toDateString())
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}