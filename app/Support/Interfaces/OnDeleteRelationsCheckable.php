<?php

namespace App\Support\Interfaces;

interface OnDeleteRelationsCheckable
{

    /**
     * Les relations a suprimé quand un model est supprimé
     */
    public function getRelationsMethods(): ?array;

}