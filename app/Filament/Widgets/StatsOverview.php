<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSales = (float) Order::whereIn('payment_status', ['paid'])
            ->sum('total_amount');

        $newMessagesCount = Message::query()
            ->where('is_seen', false)
            ->whereHas('conversation', function ($query) {
                $query->whereColumn('messages.sender_id', 'conversations.user_id');
            })
            ->count();

        return [
            Stat::make('Products', Product::count())
                ->description('Total products in the store')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Categories', Category::count())
                ->description('Total categories')
                ->icon('heroicon-o-squares-2x2')
                ->color('success'),

            Stat::make('Orders', Order::count())
                ->description('All orders')
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),

            Stat::make('Users', User::count())
                ->description('Registered users')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Open Conversations', Conversation::where('status', 'open')->count())
                ->description('Currently open chats')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('danger'),

            Stat::make('New Messages', $newMessagesCount)
                ->description('Unread customer messages')
                ->icon('heroicon-o-envelope')
                ->color('danger'),

            Stat::make('Paid Sales', number_format($totalSales, 2) . ' USD')
                ->description('Total paid revenue')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->description('Orders awaiting review')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}