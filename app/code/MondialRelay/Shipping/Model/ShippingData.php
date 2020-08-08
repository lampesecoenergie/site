<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use Magento\Framework\DataObject;

/**
 * Class ShippingData
 */
class ShippingData extends DataObject implements ShippingDataInterface
{
    /**
     * Get Code
     *
     * @return string
     */
    public function getMondialrelayCode()
    {
        return $this->getData(self::MONDIAL_RELAY_CODE);
    }

    /**
     * Get Pickup Id
     *
     * @return string
     */
    public function getMondialrelayPickupId()
    {
        return $this->getData(self::MONDIAL_RELAY_PICKUP_ID);
    }
}
