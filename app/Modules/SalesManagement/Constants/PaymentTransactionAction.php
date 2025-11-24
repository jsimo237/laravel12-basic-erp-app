<?php

namespace App\Modules\SalesManagement\Constants;

enum PaymentTransactionAction : string
{

    case CREDIT = 'CREDIT'; //
    case DEBIT = 'DEBIT'; //'
    case RESET = 'RESET'; //
    case REPAIR = 'REPAIR'; //
    const CREATED = 'CREATED';
}
