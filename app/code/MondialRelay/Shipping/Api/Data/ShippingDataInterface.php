<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Api\Data;

/**
 * Interface ShippingDataInterface
 */
interface ShippingDataInterface
{
    const MONDIAL_RELAY_CODE = 'mondialrelay_code';

    const MONDIAL_RELAY_PICKUP_ID = 'mondialrelay_pickup_id';

    const MONDIAL_RELAY_PACKAGING_WEIGHT = 'mondialrelay_packaging_weight';

    /**
     * Get Code
     *
     * @return string
     */
    public function getMondialrelayCode();

    /**
     * Get Pickup Id
     *
     * @return string
     */
    public function getMondialrelayPickupId();
}
