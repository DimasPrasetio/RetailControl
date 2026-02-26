<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Uom;
use App\Models\User;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\UomPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class     => UserPolicy::class,
        Item::class     => ItemPolicy::class,
        Brand::class    => BrandPolicy::class,
        Category::class => CategoryPolicy::class,
        Uom::class      => UomPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
