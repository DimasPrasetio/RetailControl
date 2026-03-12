<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Item;
use App\Models\AttributeDefinition;
use App\Models\StockLocation;
use App\Models\Uom;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\AttributeDefinitionPolicy;
use App\Policies\BrandPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\StockLocationPolicy;
use App\Policies\UomPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Item::class => ItemPolicy::class,
        Brand::class => BrandPolicy::class,
        Category::class => CategoryPolicy::class,
        Uom::class => UomPolicy::class,
        AttributeDefinition::class => AttributeDefinitionPolicy::class,
        Branch::class => BranchPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        StockLocation::class => StockLocationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
