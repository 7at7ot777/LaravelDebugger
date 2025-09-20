<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::morphMap([
            'debug' => \App\Models\Debug::class,
            'text' => \App\Models\Text::class,
            'number' => \App\Models\Number::class,
            'json' => \App\Models\Json::class,
        ]);
//        if (
//            !request()->is(config('debugger.route_name')) &&
//            !request()->is('livewire/update') &&
//            config('debugger.truncate_tables')
//        ) {
//            Debugger::clearAllDebugData();
//        }
    }
}
