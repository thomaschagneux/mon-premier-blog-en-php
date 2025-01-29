<?php

namespace App\Services;

use Symfony\Component\VarDumper\VarDumper;

/**
 * Class HelperServices
 * Provides utility methods for debugging, including dumping variables and stopping script execution.
 */
class HelperServices
{
    /**
     * Dumps the provided variable using Symfony's VarDumper without stopping the script execution.
     *
     * @param mixed $var The variable to dump.
     *
     * @return void
     */
    public function dump(mixed $var): void
    {
        VarDumper::dump($var);
    }

    /**
     * Dumps the provided variable using Symfony's VarDumper and stops script execution.
     *
     * @param mixed $var The variable to dump.
     *
     * @return void
     */
    public function dd(mixed $var): void
    {
        VarDumper::dump($var);
        die();
    }
}
