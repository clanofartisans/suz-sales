<?php

namespace Tests\Faker;

/**
 * @link https://github.com/fzaninotto/Faker/pull/2012
 */
class CustomGenerators extends \Faker\Provider\Base
{
    /**
     * Utility function for computing UPC checksums.
     *
     * @param string $input
     *
     * @return int
     */
    protected static function upcChecksum($input)
    {
        $split = str_split($input);

        $mod = (($split[0] + $split[2] + $split[4] + $split[6] + $split[8] + $split[10]) * 3
                + $split[1] + $split[3] + $split[5] + $split[7] + $split[9]) % 10;

        return $mod == 0 ? 0 : 10 - $mod;
    }

    /**
     * Get a random UPC-A barcode.
     * @link http://en.wikipedia.org/wiki/Universal_Product_Code
     *
     * @return string
     * @example '725272730706'
     */
    public function upc()
    {
        $code = static::numerify(str_repeat('#', 11));

        return $code.static::upcChecksum($code);
    }
}