<?php

namespace App\POS;

use App\POS\Drivers\CounterpointDriver;
use App\POS\Drivers\OrderDogDriver;
use Illuminate\Support\Manager;

class POSManager extends Manager
{
    /**
     * Create an instance of the Counterpoint POS Driver.
     *
     * @return \App\POS\Drivers\CounterpointDriver
     */
    public function createCounterpointDriver()
    {
        return new CounterpointDriver;
    }

    /**
     * Create an instance of the OrderDog POS Driver.
     *
     * @return \App\POS\Drivers\OrderDogDriver
     */
    public function createOrderDogDriver()
    {
        return new OrderDogDriver;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('pos.driver');
    }
}
