<?php

namespace App\Http\Middleware;

use Closure;

class CleanInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Helpers::globalXssClean();
        return $next($request);
    }
}
