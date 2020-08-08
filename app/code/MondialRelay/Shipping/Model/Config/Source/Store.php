<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Store
 */
class Store implements ArrayInterface
{

    /**
     * @var StoreManagerInterface $store
     */
    protected $store;

    /**
     * @param StoreManagerInterface $store
     */
    public function __construct(StoreManagerInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $websites = $this->store->getWebsites(true);
        $stores   = $this->store->getStores(true);

        $values = [];

        foreach ($websites as $website) {
            foreach ($stores as $store) {
                if ($store->getWebsiteId() == $website->getId()) {
                    $label = $website->getName() . ' > ' . $store->getName();
                    $values[] = [
                        'value' => $store->getId(),
                        'label' => $store->getId() ? $label : __('All Store View'),
                    ];
                }
            }
        }

        return $values;
    }
}
