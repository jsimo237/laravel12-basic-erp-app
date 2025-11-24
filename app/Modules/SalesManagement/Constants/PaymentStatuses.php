<?php

namespace App\Modules\SalesManagement\Constants;

enum PaymentStatuses : string
{

    case DRAFT = 'DRAFT'; //
    case VALIDATED = 'VALIDATED'; //
    case CANCELLED = 'CANCELLED'; //
    case REFUNDED = 'REFUNDED'; //
    case TRANSFERRED = 'TRANSFERRED'; //
    case FAILED = 'FAILED'; //
    case PENDING = 'PENDING'; //
    case INITIATED = 'INITIATED'; //


    /**
     * Retourne les valeurs des enums sous forme de tableau
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
