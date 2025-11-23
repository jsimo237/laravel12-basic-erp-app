<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kirago\BusinessCore\Facades\BusinessCore;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         *  ==== TIPS POUR LA GESTION DES ERREURS SWAGGER-PHP ===
         * Permet de ne pas avoir d’erreur de type "Notice: Required @OA\PathItem() not found"
         * Permet de Changer le niveau de log pour ignorer l’erreur
         * Par défaut, Swagger-PHP utilise trigger_error → ça casse Laravel en ErrorException.
         * En résumé, il permet de désactiver la conversion des notices en exceptions
         */
        error_reporting(E_ALL & ~E_USER_WARNING & ~E_USER_NOTICE);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BusinessCore::discoverApiRoutes(prefix : "v1");

    }
}
