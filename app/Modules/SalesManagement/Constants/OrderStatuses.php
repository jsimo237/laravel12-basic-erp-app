<?php

namespace App\Modules\SalesManagement\Constants;

enum OrderStatuses : string
{

    case DRAFT = 'DRAFT'; // La facture a été générée
    case VALIDATED = 'VALIDATED'; // La facture a été validé
    case CANCELLED = 'CANCELLED'; // La facture a été annulé

}