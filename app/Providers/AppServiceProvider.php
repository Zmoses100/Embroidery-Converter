<?php

namespace App\Providers;

use App\Models\Conversion;
use App\Models\EmbroideryFile;
use App\Policies\ConversionPolicy;
use App\Policies\EmbroideryFilePolicy;
use Illuminate\Support\Facades\Gate;
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
        // Register policies
        Gate::policy(EmbroideryFile::class, EmbroideryFilePolicy::class);
        Gate::policy(Conversion::class, ConversionPolicy::class);
    }
}
