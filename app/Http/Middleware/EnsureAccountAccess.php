<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Esta conta está desativada. Fale com o titular da conta.',
            ]);
        }

        if ($user->must_change_password && !in_array($request->route()->getName(), [
            'account.temporary-password.edit',
            'account.temporary-password.update',
            'logout',
        ], true)) {
            return redirect()->route('account.temporary-password.edit');
        }

        return $next($request);
    }
}
