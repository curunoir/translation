<?php
namespace curunoir\translation;
use Illuminate\Support\ServiceProvider;


class TranslationServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('t', function ($args) {
            return "<?php echo App::make('translation')->translate{$args}; ?>";
        });
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

        // Allow migrations to be publishable.
        $this->publishes([
            __DIR__.'/Migrations/' => base_path('/database/migrations'),
        ], 'migrations');

        // Include the helpers file for global `trad()` function
        include __DIR__.'/helpers_translation.php';
    }
}