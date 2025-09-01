<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales([
                'es' => 'Esp',
                'en' => 'Eng',
                'fr' => 'Fra',
            ])
            ->flagsOnly()   // solo banderas
            ->circular()    // banderas en círculo
            ->visible();    // siempre visible
        });

        
    }
}
