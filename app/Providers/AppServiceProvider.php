<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockLocation;
use App\Observers\CategoryObserver;
use App\Observers\ItemObserver;
use App\Observers\StockLocationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        StockLocation::observe(StockLocationObserver::class);
        Item::observe(ItemObserver::class);
    }
}
