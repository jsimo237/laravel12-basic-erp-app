<?php

namespace App\Support\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Annotations as OA;

/**
 *
 * Rôle :
 * -------
 * Ce provider permet d'indiquer à L5-Swagger de scanner également le dossier
 * `app/Modules` afin d'y détecter les annotations OpenAPI (`@OA\Schema`,
 * `@OA\Post`, etc.).
 *
 * Fonctionnement :
 * ----------------
 * - Récupère la configuration de Swagger définie dans `l5-swagger.php`.
 * - Ajoute dynamiquement le dossier `app/Modules` dans la liste des chemins
 *   analysés pour la génération de la documentation.
 * - Réinjecte cette configuration mise à jour dans le cycle de vie Laravel.
 *
 * Impact :
 * --------
 * - Les schémas peuvent être définis dans :
 *      app/Modules/<NomDuModule>/Services/OpenApi/Schemas/*
 *
 * - La documentation des endpoints peut être ajoutée directement dans :
 *      app/Modules/<NomDuModule>/Controllers/*
 *
 * Cela évite de devoir modifier manuellement le fichier
 * `config/l5-swagger.php` à chaque ajout de module : l'analyse est faite
 * automatiquement sur l'ensemble du dossier `Modules`.
 *
 * Étapes d'activation :
 * ---------------------
 * 1. Enregistrer le provider dans `config/app.php` :
 *    App\Support\Providers\OpenApiServiceProvider::class,
 *
 * 2. Vider le cache de configuration :
 *    php artisan config:clear
 *
 * 3. Générer la documentation :
 *    php artisan l5-swagger:generate
 *
 * Exemple d'utilisation :
 * -----------------------
 * Dans un contrôleur :
 *     @OA\Post(
 *         path="/auth/login",
 *         tags={"Auth"},
 *         summary="Connexion utilisateur",
 *         @OA\RequestBody(ref="#/components/schemas/LoginFormRequest"),
 *         @OA\Response(response=200, ref="#/components/schemas/LoginResponse")
 *     )
 *
 * Dans un schéma :
 *     @OA\Schema(schema="LoginFormRequest", ...)
 *     @OA\Schema(schema="LoginResponse", ...)
 *
 * L5-Swagger relie automatiquement les contrôleurs et les schémas.
 */
class OpenApiServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        // On récupère le chemin de la config L5-Swagger
        $swaggerConfigPath = config_path('l5-swagger.php');

        if (file_exists($swaggerConfigPath)) {
            // On prend la config actuelle
            $config = config('l5-swagger.documentations.default.paths');

            // On définit le chemin de nos modules
            $modulesPath = base_path('app/Modules');

            // On fusionne l’ancien tableau d’annotations avec notre dossier Modules
            $config['annotations'] = array_merge(
                                        $config['annotations'],
                                        [$modulesPath]
                                    );

            // On réinjecte la config modifiée dans Laravel
            config(['l5-swagger.documentations.default.paths' => $config]);
        }
    }
}
