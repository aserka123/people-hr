<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class HrOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isHr(), 403, 'HR access required.');
        return $next($request);
    }
}
