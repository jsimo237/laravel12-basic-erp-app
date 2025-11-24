<?php

namespace App\Modules\SalesManagement\Interfaces;


interface Billable
{
    public function produceBillPDF() : array;
}