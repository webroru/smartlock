<?php

namespace App\Helpers;

class PhoneHepler
{
    public static function clear(string $phone): string
    {
        return str_replace(['+', '(', ')', ' ', '-'], '', $phone);
    }
}
