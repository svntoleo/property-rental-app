<?php

namespace App\Providers;

use App\Models\Accommodation;
use App\Models\Property;
use App\Models\Stay;
use App\Observers\AccommodationObserver;
use App\Observers\PropertyObserver;
use App\Observers\StayObserver;
use Illuminate\Http\Resources\Json\JsonResource;
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
        // Disable JsonResource wrapping for cleaner Inertia props
        JsonResource::withoutWrapping();

        // Register model observers for cascade soft deletes
        Property::observe(PropertyObserver::class);
        Accommodation::observe(AccommodationObserver::class);
        Stay::observe(StayObserver::class);
    }
}
