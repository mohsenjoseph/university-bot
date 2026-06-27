<?php

namespace App\Helpers;

class NumberHelper
{
    public static function toEnglish(?string $value): string
    {
        if (!$value) return '';
        
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];

        $value = str_replace($persian, $english, $value);
        $value = str_replace($arabic,  $english, $value);

        return $value;
    }

    public static function onlyDigits(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', self::toEnglish($value));
    }
}