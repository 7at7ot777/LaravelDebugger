<?php

use App\Debugger;
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
    $debugController = new \App\Http\Controllers\DatabaseDebugger();

      return ($debugController->getStackTrace());
});

Route::get('/test', function () {
//    $debugController = new \App\Http\Controllers\DatabaseDebugger();

    $largeArray = [];

    for ($i = 0; $i < 100; $i++) {
        $largeArray[] = [
            'id' => $i + 1,
            'uuid' => (string) Str::uuid(),
            'name' => 'Item ' . ($i + 1),
            'price' => rand(100, 10000) / 100,
            'tags' => ['test', 'sample', 'demo'],
            'metadata' => [
                'created_by' => 'system',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->addMinutes(rand(1, 100))->toDateTimeString(),
                'active' => (bool) rand(0, 1),
            ],
            'details' => [
                'description' => Str::random(200),
                'notes' => Str::random(100),
                'nested' => [
                    'level_1' => [
                        'level_2' => [
                            'value' => rand(1, 999),
                            'flag' => (bool) rand(0, 1),
                        ]
                    ]
                ]
            ]
        ];
    }

    $largeJson = json_encode($largeArray, JSON_PRETTY_PRINT);

    $jsonc = <<<JSON
{
"test": "test"
}
JSON;


    $query = \App\Models\Text::select('text');
     Debugger::displayQuery($query);
     Debugger::display(1);
     Debugger::display(1.5);
     Debugger::display(['test' => 'test']);;
     Debugger::display([1,2,3]);
     Debugger::display(json_decode($jsonc));;
     Debugger::display(true);
     Debugger::display(false);
     Debugger::display('Hello, World!');
     Debugger::display($largeJson);
});


Route::get('truncate', function () {
    \App\Models\Text::truncate();
    \App\Models\Json::truncate();
    \App\Models\Number::truncate();
    \App\Models\Debug::truncate();

})->name('truncate');
