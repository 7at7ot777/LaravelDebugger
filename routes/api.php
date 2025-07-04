<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/test', function () {
    $debugController = new \App\Http\Controllers\DatabaseDebugger();
    $array = [
        'name' => 'John Doe',
        'age' => 30,
        'is_active' => true,
        'balance' => 1234.56,
        'items.0' => 'item1',
        'items.1' => 'item2',
        'items.2' => 'item3',
        'details.address' => '123 Main St',
        'details.city' => 'Anytown',
    ]
;
    $debugController->displayQuery(\App\Models\Debug::where('id', 1)->where('text', 'like', '%test%'));
    $debugController->display($array);
    $debugController->display(1);
    $debugController->display(1.35);
    $debugController->display(true);
    $debugController->display(false);
    $debugController->display('Hello, World!');
    $debugController->display(\App\Models\Debug::get()->toArray());
})->middleware(\App\Http\Middleware\DebuggerMiddleware::class);
