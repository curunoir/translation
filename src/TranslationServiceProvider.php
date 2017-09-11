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
        $this->app->singleton(TranslationLib::class, function () {
            return new TranslationLib();
        });
        $this->app->alias(TranslationLib::class, 'translationlib');

        // Include the helpers file for global `trad()` function
        include __DIR__.'/helpers_translation.php';
    }
}