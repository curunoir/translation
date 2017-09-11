<?php

namespace curunoir\translation\\Middlewares;

use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use curunoir\Translation\Models\Locale;

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
        $locale = $request->segment(1);
        if (PHP_SAPI == 'cli' && strpos($_SERVER['argv'][0], 'phpunit') !== FALSE) {
            return $next($request);
        }
        if ($locale == 'maintenances') {
            return $next($request);
        }

        $redirect = false;

        if ($request->ajax()) {
            return $next($request);
        }

        if ($locale != 'login'
            && $locale != 'logout'
            && $locale != '_debugbar'
            && $locale != 'password'
            && $locale != 'test'
            && $locale != 'logs'
            && $locale != 'ajax'
            && $locale != 'datadevices'
            && $locale != 'maintenances'
        ) {
            if (session('code')):

                $this->app->setLocale(session('code'));
                return $this->redirector->to('/' . session('code'));
            else:
                $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
                $locales = Locale::pluck('code')->toArray();

                if (in_array($locale, $locales)):
                    $this->app->setLocale($locale);
                    return $this->redirector->to('/' . $locale);
                else:
                    $this->app->setLocale($locale);
                    return $this->redirector->to('/' . config('app.locale'));
                endif;
            endif;
        }
        if ($locale == 'auth' || $locale == 'password'):
            $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            session(['code' => $locale]);

        endif;
        if (session('code') && session('code') != $locale):
            session(['code' => $locale]);
        endif;

        $this->app->setLocale($locale);

        if ($redirect) {
            return $this->redirector->to('/' . $locale);
        }

        return $next($request);
    }
}
