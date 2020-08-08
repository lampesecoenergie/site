<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Rate;

use Magento\Shipping\Model\Rate\Result as RateResult;

/**
 * Class Result
 */
class Result extends RateResult
{
    /**
     * Avoid to sort rates by price
     *
     * @return $this
     */
    public function sortRatesByPrice()
    {
        return $this;
    }
}
