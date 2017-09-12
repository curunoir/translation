# Translation

Package for static and dynamic translation within Laravel projects

## Installation

Require the translation package 

    composer require curunoir/translation

Add the service provider to your `config/app.php` config file

    'curunoir\translation\TranslationServiceProvider',
    
Add the facade to your aliases in your `config/app.php` config file

    'translationlib' => 'curunoir\translation\Facades\Translation',
    
Publish the migrations

    php artisan vendor:publish --provider="curunoir\translation\TranslationServiceProvider"
    
Run the migrations

    php artisan migrate
