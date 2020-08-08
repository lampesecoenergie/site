<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Frontend\Pickup\Load;

use MondialRelay\Shipping\Block\Frontend\Pickup\Load;

/**
 * Class Debug
 */
class Debug extends Load
{
    /**
     * Retrieve calculation type
     *
     * @param string $type
     * @return string
     */
    public function getCalculation($type)
    {
        return $this->shippingHelper->getCalculation($type);
    }

    /**
     * Retrieve calculation type
     *
     * @param string $type
     * @param bool $text
     * @return string
     */
    public function isLimitationActive($type, $text = false)
    {
        if (!$text) {
            return $this->shippingHelper->isLimitationActive($type);
        }

        return $this->shippingHelper->isLimitationActive($type) ? __('Yes') : __('No');
    }

    /**
     * Retrieve pickup config
     *
     * @return array
     */
    public function getPickupConfig()
    {
        return $this->shippingHelper->getConfig('pickup/limits');
    }
}
