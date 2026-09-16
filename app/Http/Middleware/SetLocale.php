<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'bn'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale') ?? $request->user()?->locale ?? 'en';

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        if ($tz = $request->user()?->timezone) {
            config(['app.timezone' => $tz]);
            date_default_timezone_set($tz);
        }

        return $next($request);
    }
}
