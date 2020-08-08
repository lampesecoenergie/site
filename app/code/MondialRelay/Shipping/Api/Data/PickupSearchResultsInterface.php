<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface PickupSearchResultsInterface
 */
interface PickupSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pickup list.
     *
     * @return \MondialRelay\Shipping\Api\Data\PickupInterface[]
     */
    public function getItems();
}
