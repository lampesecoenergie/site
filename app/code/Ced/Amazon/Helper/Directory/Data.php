<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper\Directory;

use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Directory\Helper\Data
{
    public $amazonOrderRegistry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Ced\Amazon\Registry\Order $order = null
    ) {
        parent::__construct(
            $context,
            $configCacheType,
            $countryCollection,
            $regCollectionFactory,
            $jsonHelper,
            $storeManager,
            $currencyFactory
        );
        $this->amazonOrderRegistry = $order !== null ?
            $order : ObjectManager::getInstance()->get(\Ced\Amazon\Registry\Order::class);
    }

    /**
     * Retrieve country collection
     *
     * @param null|int|string|\Magento\Store\Model\Store $store
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection($store = null)
    {
        if (!$this->_countryCollection->isLoaded()) {
            $this->_countryCollection->loadByStore($store);
        }

        if ($this->amazonOrderRegistry && $country = $this->amazonOrderRegistry->getCountryCode()) {
            $countries = $this->_countryCollection->getAllIds();
            if (!in_array($country, $countries)) {
                $countries[] = $country;
                $this->_countryCollection->getSelect()->reset('where');
                $this->_countryCollection->addFieldToFilter('country_id', ['in' => $countries]);
            }
        }

        return $this->_countryCollection;
    }
}
