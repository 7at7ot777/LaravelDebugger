<?php

use App\Debugger;
use App\DebuggerInterface;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

    Route::get(config('debugger.route_name'), \App\Livewire\DebugViewer::class)->name(config('debugger.route_name'));






Route::get('/test', function () {

    Debugger::display(1);
    Debugger::display(1.35);
    Debugger::display(true);
    Debugger::display(false);
    Debugger::display('Hello, World!');
    Debugger::display(\App\Models\Debug::get()->toArray());
});


Route::get('truncate', function () {
    \App\Models\Text::truncate();
    \App\Models\Json::truncate();
    \App\Models\Number::truncate();
    \App\Models\Debug::truncate();

})->name('truncate');
