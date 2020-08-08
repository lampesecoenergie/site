<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface PickupRepositoryInterface
 */
interface PickupRepositoryInterface
{
    /**
     * Retrieve current pickup data for quote
     *
     * @param string $cartId
     * @return \MondialRelay\Shipping\Api\Data\PickupInterface
     */
    public function current($cartId);

    /**
     * Save pickup data for quote
     *
     * @param string $cartId
     * @param string $pickupId
     * @param string $countryId
     * @param string $code
     * @return bool
     */
    public function save($cartId, $pickupId, $countryId, $code);

    /**
     * Reset pickup data for quote
     *
     * @param string $cartId
     * @return bool
     */
    public function reset($cartId);

    /**
     * Get pickup
     *
     * @param string $pickupId
     * @param string $countryId
     * @return \MondialRelay\Shipping\Api\Data\PickupInterface
     */
    public function get($pickupId, $countryId);

    /**
     * Get pickup list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \MondialRelay\Shipping\Api\Data\PickupSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get shipping data for order
     *
     * @param int $orderId
     * @return \MondialRelay\Shipping\Api\Data\ShippingDataInterface
     */
    public function shippingData($orderId);
}
