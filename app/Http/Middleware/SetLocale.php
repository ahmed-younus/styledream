<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supportedLocales = ['en', 'es', 'fr', 'de', 'it', 'pt', 'nl'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        if (in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Determine the locale to use.
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Check query parameter FIRST (for switching languages via dropdown)
        if ($request->has('lang') && in_array($request->get('lang'), $this->supportedLocales)) {
            $locale = $request->get('lang');
            $request->session()->put('locale', $locale);

            // Also update user preference if logged in
            if (Auth::check()) {
                Auth::user()->update(['locale' => $locale]);
            }

            return $locale;
        }

        // 2. Check if user is authenticated and has a locale preference
        if (Auth::check() && Auth::user()->locale) {
            return Auth::user()->locale;
        }

        // 3. Check session
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // 4. Check browser preference
        $browserLocale = $request->getPreferredLanguage($this->supportedLocales);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Default to config locale
        return config('app.locale', 'en');
    }
}
