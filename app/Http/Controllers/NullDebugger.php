<?php

namespace App\Http\Controllers;

use App\DebuggerInterface;
use App\Http\Controllers\Controller;
use App\Models\Debug;
use App\Models\Json;
use App\Models\Number;
use App\Models\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NullDebugger implements DebuggerInterface
{
    public function __construct()
    {
    }
    public function display($variable): void
    {

    }

    public function displayQuery(Builder $query): void
    {}
    public function getStackTrace()
    {

    }
    private function refreshDB()
    {
    }

    public function loadDebugData($search = null, $filterByType = null, $filterByFile = null): array
    {
        return [];
    }

    public function loadFiles(): array
    {
        return [];
    }

    public function clearAllDebugData(): void
    {
    }
}
