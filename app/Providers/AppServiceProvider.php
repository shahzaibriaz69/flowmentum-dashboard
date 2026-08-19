<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\ComponentAttributeBag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * The published BlatUI Blade components use `twMerge()`. Its usual
         * peer package is incompatible with Laravel 13's Guzzle 8 dependency,
         * so provide the small attribute-bag macro locally. User classes are
         * appended after the component defaults, allowing them to override the
         * defaults in Tailwind's generated stylesheet.
         */
        ComponentAttributeBag::macro('twMerge', function (string ...$classes): ComponentAttributeBag {
            $classNames = array_filter([
                ...$classes,
                $this->get('class'),
            ], fn ($className) => filled($className));

            $this->offsetSet('class', implode(' ', $classNames));

            return $this;
        });
    }
}
