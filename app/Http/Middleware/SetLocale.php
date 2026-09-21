<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('app.supported_locales', []));
        $locale = $request->user()?->locale;

        if (! is_string($locale) || ! in_array($locale, $supportedLocales, true)) {
            $locale = $request->cookie(config('app.locale_cookie'));
        }

        if (! is_string($locale) || ! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
