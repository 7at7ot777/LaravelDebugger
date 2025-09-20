<?php

use Illuminate\Support\Facades\Route;



    Route::get(config('debugger.route_name'), \App\Livewire\DebugViewer::class)->name(config('debugger.route_name'));

