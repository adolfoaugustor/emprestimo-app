<?php

namespace App\Http\Middleware;

use App\Services\AccountContext;
use Closure;
use Illuminate\Http\Request;

class RequireAccountPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        abort_unless($request->user() && app(AccountContext::class)->canPerform($request->user(), $permission), 403);

        return $next($request);
    }
}
