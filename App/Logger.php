<?php

namespace App;

class Logger
{
    public static function log(string $message): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        echo "$date $message\n";
    }
}
