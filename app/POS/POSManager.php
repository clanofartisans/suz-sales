<?php namespace App\POS;

use Illuminate\Support\Manager;
use App\POS\Drivers\OrderDogDriver;
use App\POS\Drivers\CounterpointDriver;

/**
 * Class POSManager
 */
class POSManager extends Manager
{
    /**
     * Get the default POS driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['pos.driver'];
    }

    /**
     * Create an instance of the OrderDog POS driver.
     *
     * @return \App\POS\Drivers\OrderDogDriver
     */
    protected function createOrderDogDriver()
    {
        return new OrderDogDriver;
    }

    /**
     * Create an instance of the Counterpoint POS driver.
     *
     * @return \App\POS\Drivers\CounterpointDriver
     */
    protected function createCounterpointDriver()
    {
        return new CounterpointDriver;
    }
}
