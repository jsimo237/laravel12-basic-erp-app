<?php

namespace App\Modules\SalesManagement\Constants;

enum PaymentTransactionStatuses : string
{
    const CREATED = 'CREATED';
    const PENDING = 'PENDING';
    const FAILED = 'FAILED';
    const CANCELLED = 'CANCELLED';
    const SUCCESSFULL = 'SUCCESSFULL';
}
