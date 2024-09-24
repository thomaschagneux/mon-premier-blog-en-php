<?php

namespace App\Services;

use Symfony\Component\VarDumper\VarDumper;

class HelperServices
{
    public function dump(mixed $var): void
    {
        VarDumper::dump($var);
    }

    public function dd(mixed $var): void
    {
        VarDumper::dump($var);
        die();
    }
}
