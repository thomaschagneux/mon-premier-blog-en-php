<?php

namespace App\Services;

use Symfony\Component\VarDumper\VarDumper;

class HelperServices
{
    public function dump($var): void
    {
        VarDumper::dump($var);
    }

    public function dd($var): void
    {
        VarDumper::dump($var);
        die();
    }
}
