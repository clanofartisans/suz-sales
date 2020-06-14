<?php

namespace App\POS;

use Illuminate\Support\Facades\Facade;

/**
 * Class POSFacade.
 */
class POSFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pos';
    }
}
