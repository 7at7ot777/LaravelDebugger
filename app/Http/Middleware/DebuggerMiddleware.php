<?php

namespace App\Http\Middleware;

use App\Models\Json;
use App\Models\Number;
use App\Models\Text;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Debug;

class DebuggerMiddleware
{
    private $excludeRoutes;

    public function __construct()
    {
        // Exclude the debug viewer route from being logged
        $this->excludeRoutes = [route(config('debugger.route_name'))];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->url(), $this->excludeRoutes)) {
            return $next($request);
        }
        if(config('debugger.truncate_tables'))
        {
            Text::truncate();
            Json::truncate();
            Number::truncate();
            Debug::truncate();
        }
        return $next($request);

    }
}
