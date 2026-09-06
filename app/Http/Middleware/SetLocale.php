<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = array_map('strval', array_keys(config('app.supported_locales')));
        $locale = (Auth::guard('web')->check() ? Auth::guard('web')->user()->locale : null)
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage($locales);

        App::setLocale(in_array($locale, $locales, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
