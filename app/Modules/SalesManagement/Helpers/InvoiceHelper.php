<?php

namespace App\Modules\SalesManagement\Helpers;

use Carbon\Carbon;
use App\Modules\SalesManagement\Constants\BillingInformations;
use App\Modules\SalesManagement\Constants\InvoiceStatuses;
use App\Modules\SalesManagement\Constants\OrderStatuses;
use App\Modules\SalesManagement\Interfaces\BaseOrderContract;
use App\Modules\SalesManagement\Models\BaseOrder;
use App\Modules\SalesManagement\Models\Invoice;

final class InvoiceHelper
{

    public static function generateInvoiceForOrder(BaseOrderContract|BaseOrder $order): Invoice
    {
        $invoice = $order->invoice ?? new Invoice();

        $recipient = $order->recipient;

        $invoice->status = InvoiceStatuses::VALIDATED->value;
        $invoice->billing_entity_type = BillingInformations::TYPE_INDIVIDUAL->value;
        $invoice->expired_at = $order->expired_at ?? Carbon::now()->addDays(60);
        $invoice->discounts = $order->discounts ?? [];

        $invoice->billing_company_name    = $order->billing_company_name ?? "---";
        $invoice->billing_firstname       = $order->billing_firstname ?? "---";
        $invoice->billing_lastname        = $order->billing_lastname ?? "---";
        $invoice->billing_country         = $order->billing_country ?? "---";
        $invoice->billing_state           = $order->billing_state ?? "---";
        $invoice->billing_city            = $order->billing_city ?? "---";
        $invoice->billing_zipcode         = $order->billing_zipcode ?? "---";
        $invoice->billing_address         = $order->billing_address ?? "---";
        $invoice->billing_email           = $order->billing_email ?? "---";

        $invoice->order()->associate($order);
        $invoice->recipient()->associate($recipient);
        $invoice->organization()->associate($order->organization);
        $invoice->save();

        $order->status = OrderStatuses::VALIDATED;
        $order->save();

        return $invoice;
    }

}