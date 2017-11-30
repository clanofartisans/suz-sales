<?php namespace App\POS\Drivers;

use App\POS\POS;
use App\POS\Contracts\POSDriverInterface as POSDriverContract;

/**
 * Class CounterpointDriver
 */
class CounterpointDriver extends POS implements POSDriverContract
{
    public function test()
    {
        echo("Counterpoint Test\n");
    }
}
