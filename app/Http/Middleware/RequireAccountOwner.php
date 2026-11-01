<?php

namespace App\Http\Middleware;

use App\Services\AccountContext;
use Closure;
use Illuminate\Http\Request;

class RequireAccountOwner
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() && app(AccountContext::class)->isOwner($request->user()), 403);

        return $next($request);
    }
}
