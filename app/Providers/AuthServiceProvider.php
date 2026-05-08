<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Policies\BasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Category::class => BasePolicy::class,
        Product::class => BasePolicy::class,
        Order::class => BasePolicy::class,
        Address::class => BasePolicy::class,
        Conversation::class => BasePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}