<?php

use App\Debugger;
use App\DebuggerInterface;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use function MongoDB\BSON\toJSON;

Route::get('/', function () {
    return view('welcome');
})->name('home');

//if(config('debugger.is_enabled')){
    Route::get(config('debugger.route_name'), \App\Livewire\DebugViewer::class)->name(config('debugger.route_name'));
//}

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';




Route::get('/test', function () {

});


Route::get('truncate', function () {
    \App\Models\Text::truncate();
    \App\Models\Json::truncate();
    \App\Models\Number::truncate();
    \App\Models\Debug::truncate();

})->name('truncate');
