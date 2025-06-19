<?php

namespace App\Providers;

use App\Models\Debug;
use App\Models\Json;
use App\Models\Number;
use App\Models\Text;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
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

//        if(config('debugger.refresh_database'))
//        {
//            Text::truncate();
//            Json::truncate();
//            Number::truncate();
//            Debug::truncate();
//        }
    }
}
