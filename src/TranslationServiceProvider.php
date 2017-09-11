<?php
namespace curunoir\translation;
use Illuminate\Support\ServiceProvider;
use Stevebauman\Translation\TranslationServiceProvider as SBTServiceProvider;

class TranslationServiceProvider extends SBTServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Translation::class, function () {
            return new Translation();
        });
        $this->app->alias(Translation::class, 'translation');
    }
}