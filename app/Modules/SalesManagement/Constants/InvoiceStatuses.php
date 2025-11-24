<?php

namespace App\Modules\SalesManagement\Constants;

enum InvoiceStatuses : string
{

    case DRAFT = 'DRAFT'; // La facture est en brouillon
    case CREATED = 'CREATED'; // La facture a été crée
    case VALIDATED = 'VALIDATED'; // La facture a été validée
    case CANCELLED = 'CANCELLED'; // La facture a été annulée
    case PAID = 'PAID'; // La facture a été payée
    case OVERDUE = 'OVERDUE'; // La facture est en retard de paiement
    case PENDING = 'PENDING'; // La facture est en retard de paiement

}
