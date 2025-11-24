<?php

namespace App\Support\Contracts;

interface GenerateUniqueValueContrat
{
    public function generateUniqueValue(string $field = "code") : void ;
}
