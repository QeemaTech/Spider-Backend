<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'ar'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isApiRequest = $request->is('api/*');

        if ($isApiRequest) {
            // API priority: query/header first, then session, then app default.
            $locale = $this->resolveFromRequest($request);
            if ($locale === '' && Session::has('locale')) {
                $locale = (string) Session::get('locale');
            }
        } else {
            // Dashboard/web priority: session first (locale switcher), then query/header fallback.
            $locale = Session::has('locale') ? (string) Session::get('locale') : '';
            if ($locale === '') {
                $locale = $this->resolveFromRequest($request);
            }
        }

        if (! in_array($locale, $this->supportedLocales, true)) {
            $locale = (string) config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function resolveFromRequest(Request $request): string
    {
        $locale = (string) ($request->query('lang') ?? $request->input('lang') ?? '');
        if ($locale !== '') {
            return strtolower(trim($locale));
        }

        $header = strtolower((string) $request->header('Accept-Language', ''));
        if ($header === '') {
            return '';
        }

        // Support values like: "ar", "ar-EG", "ar,en;q=0.9"
        $primary = explode(',', $header)[0] ?? '';
        $primary = explode('-', $primary)[0] ?? '';

        return trim($primary);
    }
}
