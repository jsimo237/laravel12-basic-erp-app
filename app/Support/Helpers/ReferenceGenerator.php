<?php

namespace App\Support\Helpers;

use App\Modules\OrganizationManagement\Models\Organization;
use App\Support\Exceptions\NewIdCannotGeneratedException;

class ReferenceGenerator
{
    /**
     * Génère une référence unique pour un modèle donné
     *
     * @param string $modelClass Le class name du modèle
     * @param Organization|null $organization Organisation associée
     * @param ?string $prefix Préfixe personnalisé (optionnel)
     * @param ?string $suffix Suffixe personnalisé (optionnel)
     * @param array $options
     * @return string
     *
     * @throws NewIdCannotGeneratedException
     * @throws \Kirago\BusinessCore\Support\Exceptions\NewIdCannotGeneratedException
     */
    public static function generate(
        string $modelClass,
        ?Organization $organization = null,
        ?string $prefix = '',
        ?string $suffix = '',
        array $options = [],
    ): string
    {
        $organization ??= $modelClass::make()->getOrganization() ?? null;

        $prefix = $prefix ?: "SUB" . $organization?->getKey();
        $suffix = $suffix ?: date("Ym");

        $options = array_merge(
                        [
                            "key" => "reference",
                            "prefix" => $prefix,
                            "suffix" => $suffix,
                            "separator" => "-",
                            "charLengthNextId" => 3,
                            "uniquesBy" => [
                                ["column" => "organization_id", "value" => $organization?->getKey()]
                            ],
                            "countBy" => [
                                ["column" => "organization_id", "value" => $organization?->getKey()],
                                ["column" => "created_at", "value" => date("Y-m")],
                            ]
                        ],
                        $options
                    );

        return newId($modelClass, $options);
    }
}
