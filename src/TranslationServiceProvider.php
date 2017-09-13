<?php
namespace curunoir\translation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use curunoir\translation\Contracts\Translation as TranslationInterface;

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
            return "<?php echo App::make('translationstatic')->translate{$args}; ?>";
        });

        $this->loadMigrationsFrom(__DIR__.'/Migrations');
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Allow configuration to be publishable.
        $this->publishes([
            __DIR__.'/Config/config.php' => config_path('translation.php'),
        ], 'config');

        // Allow migrations to be publishable.
        /*$this->publishes([
            __DIR__.'/Migrations/' => base_path('/database/migrations'),
        ], 'migrations');*/

        $this->app->singleton('translationstatic', function ($app) {
            return new TranslationStatic($this->app);
        });

        // Bind translation contract to IoC.
        $this->app->bind(TranslationInterface::class, 'translationstatic');

        // Include the helpers file for global `trad()` function
        include __DIR__.'/helpers_translation.php';
    }
}