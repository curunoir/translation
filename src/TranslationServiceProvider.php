<?php
namespace curunoir\translation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;


class TranslationServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('t', function ($args) {
            return "<?php echo App::make('translationlib')->getCacheTrad{$args}; ?>";
        });
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('translationlib', function ($app) {
            return new TranslationLib();
        });

        // Allow migrations to be publishable.
        $this->publishes([
            __DIR__.'/Migrations/' => base_path('/database/migrations'),
        ], 'migrations');

        // Include the helpers file for global `trad()` function
        //include __DIR__.'/helpers_translation.php';
    }
}