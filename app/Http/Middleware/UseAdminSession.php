<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $cookie = config('session.cookie');
        $path = config('session.path');
        config(['session.cookie' => "{$cookie}_admin", 'session.path' => '/admin']);

        try {
            return $next($request);
        } finally {
            config(['session.cookie' => $cookie, 'session.path' => $path]);
        }
    }
}
