<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Account;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Store
 * @package Ced\Amazon\Model\Source
 */
class Store extends AbstractSource
{
    /** @var \Magento\Store\Model\StoreManagerInterface  */
    public $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        /** @var \Magento\Store\Api\Data\StoreInterface[] $stores */
        $stores = $this->storeManager->getStores(true);
        $options = [];

        foreach ($stores as $store) {
            $options[] = [
                'label' => $store->getName().' - code: ' . $store->getCode() . '| id: '. $store->getId(),
                'value' => $store->getId()
            ];
        }

        return $options;
    }
}
