<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestConversations;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            OrdersChart::class,
            LatestOrders::class,
            LatestConversations::class,
        ];
    }

    public function getWidgets(): array
    {
        return [];
    }
}