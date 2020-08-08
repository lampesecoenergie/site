<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source\Country;

use MondialRelay\Shipping\Model\Config\Source\Country;

/**
 * Class Confort
 */
class Confort extends Country
{
    /**
     * Countries setter
     */
    public function setCountries()
    {
        $this->countries = $this->shippingHelper->getConfig('confort/countries');
    }
}
