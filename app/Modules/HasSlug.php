<?php

namespace App\Modules;

trait HasSlug
{

    public static function findBySlug($sulg) : self{
        return self::firstWhere('slug',$sulg);
    }
}