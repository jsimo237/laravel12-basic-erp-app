<?php

namespace App\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionException;

trait UsesUuidV6
{


   /**
   * Boot the trait.
   *
   * Génère automatiquement un UUIDv6 à la création du modèle et
   * configure la clé primaire comme string et non auto-incrémentée.
   */
    protected static function bootUsesUuidV6(): void
    {
        // Configurer automatiquement le modèle
       // static::configureModel();

        // Générer un UUIDv6 avant création
        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = self::generateUuid6();
            }
        });
    }

//    public function __construct()
//    {
//        $this->definePrimaryKeysConfigs();
//    }


    /**
     * Configure automatiquement $keyType et $incrementing du modèle.
     * @throws ReflectionException
     */
    protected static function configureModel(): void
    {
        // Note : $model = new static; ne fonctionne pas dans static context
        $class = static::class;

        // Utilise une ReflectionClass pour définir les propriétés protégées
        $reflection = new \ReflectionClass($class);
        if ($reflection->hasProperty('keyType')) {
            $prop = $reflection->getProperty('keyType');
            $prop->setValue($class, 'string');
        }

        if ($reflection->hasProperty('incrementing')) {
            $prop = $reflection->getProperty('incrementing');
            $prop->setValue($class, false);
        }
    }

    /**
     * Appliquer la configuration de la clé primaire
     */
    protected function definePrimaryKeysConfigs()
    {
        /**
         * Définir la colonne qui est utilisée comme clé primaire
         *
         * @var string
         */
        $this->primaryKey = "id";

        /**
         * Définir le type de clé primaire comme étant une chaîne de caractères
         *
         * @var string
         */
        $this->keyType = "string";

        /**
         * Indiquer que la clé primaire est auto-incrémentée
         *
         * @var bool
         */
        $this->incrementing = false;
    }

    /**
     * Génère un UUIDv6 partiellement séquentiel.
     */
    public static function generateUuid6(): string
    {
        // Utilise le package ramsey/uuid ou un fallback simple
        if (class_exists(\Ramsey\Uuid\Uuid::class)) {
            return \Ramsey\Uuid\Uuid::uuid6()->toString();
        }


        // Fallback simple : timestamp + random
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x6000, // version 6
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Met à jour les enregistrements qui n'ont pas un UUIDv6 valide
     * dans la colonne spécifiée (par défaut: "id").
     *
     * @param string $column Nom de la colonne à vérifier.
     * @return int Nombre d'enregistrements mis à jour.
     */
    public static function fixInvalidUuids(string $column = 'id'): int
    {
        $model = new static;
        $table = $model->getTable();
        $count = 0;

        // Récupérer les lignes où la colonne n'est pas un UUIDv6 valide
        $rows = DB::table($table)->get([$column]);

        foreach ($rows as $row) {
            if (! self::isValidUuidV6($row->{$column})) {
                $newUuid = self::generateUuid6();
                DB::table($table)
                    ->where($column, $row->{$column})
                    ->update([$column => $newUuid]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Vérifie si une valeur est un UUIDv6 valide.
     *
     * @param string|null $value
     * @return bool
     */
    public static function isValidUuidV6(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Vérifie la forme générale d’un UUID
        if (! preg_match('/^[0-9a-fA-F\-]{36}$/', $value)) {
            return false;
        }

        // Vérifie que la version = 6 (le premier chiffre du 3e bloc = 6)
        $version = substr($value, 14, 1);
        return $version === '6';
    }

}
