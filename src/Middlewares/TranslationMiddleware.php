<?php

namespace curunoir\translation\Middlewares;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use curunoir\Translation\Models\Locale;
use curunoir\Translation\Facades\TranslationStatic;

class TranslationMiddleware
{
    public function __construct(Application $app, Redirector $redirector, Request $request)
    {
        $this->app = $app;
        $this->redirector = $redirector;
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax() || (PHP_SAPI == 'cli' && strpos($_SERVER['argv'][0], 'phpunit')))
            return $next($request);

        $forget = false;
        $newCookie = false;
        $routePrefix = TranslationStatic::getRoutePrefix();
        if ($request->hasCookie('locale')) {
            $locale = $this->request->cookie('locale');
            // We change the cookie value if the user changed its url prefix
            if($routePrefix != $locale) {
                $forget = true;
                $locale = $routePrefix;
            }
        }
        else {
            $locale = $routePrefix;

            if(in_array($locale, TranslationStatic::getConfigUntranslatableActions()))
                return $next($request);

            if(!in_array($locale, array_keys(TranslationStatic::getConfigAllowedLocales())))
                $locale = TranslationStatic::getConfigDefaultLocale();
            $newCookie = true;
        }

        TranslationStatic::setLocale($locale);
        $this->app->setLocale($locale);

        $segment = $this->request->segment(TranslationStatic::getConfigRequestSegment());

        if ($request->path() == '/' )
            return $this->redirector->to('/' . $locale);
        else if(!in_array($segment, array_keys(TranslationStatic::getConfigAllowedLocales())))
            return $this->redirector->to('/' . $locale . '/' . $request->path() );

        // Setting cookie
        $response = $next($request);
        if($newCookie)
            return $response->cookie('locale', $locale, 3600);
        elseif($forget)
            return $response->withCookie(\Cookie::forget('locale'));
        else
            return $response;
    }
}
