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

namespace Ced\Amazon\Observer\Order;

use Magento\Framework\Event\ObserverInterface;
use Ced\Amazon\Helper\Product\Inventory;
use Ced\Amazon\Helper\Config;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Model\Profile\Product;

class Create implements ObserverInterface
{
    /**
     * Amazon Logger
     * @var \Ced\Amazon\Helper\Logger
     */
    private $logger;

    /** @var Config */
    private $config;

    /** @var Inventory */
    private $inventory;

    /** @var Product  */
    private $product;

    /**
     * Change constructor.
     * @param Logger $logger
     * @param Inventory $inventory
     * @param Product $product
     * @param Config $config
     */
    public function __construct(
        Logger $logger,
        Inventory $inventory,
        Product $product,
        Config $config
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->product = $product;
        $this->inventory = $inventory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->config->getInventorySync()) {
                /** @var \Magento\Framework\Event  $event */
                $event = $observer->getEvent();

                if ($event->hasData('quote')) {
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $event->getData('quote');
                    $productIds = [];
                    /** @var \Magento\Quote\Model\Quote\Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        $storeId = $item->getStoreId();
                        if ($this->product->isMarketplaceProduct($item->getProductId(), $storeId)) {
                            $productIds[$item->getProductId()] = $item->getProductId();
                        }

                        $children = $item->getChildrenItems();
                        if ($children) {
                            foreach ($children as $childItem) {
                                $storeId = $item->getStoreId();
                                if ($this->product->isMarketplaceProduct($childItem->getProductId(), $storeId)) {
                                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                                }
                            }
                        }
                    }

                    if (!empty($productIds)) {
                        $this->inventory->update($productIds, true, \Ced\Amazon\Model\Source\Queue\Priorty::HIGH);
                    }
                }
            }
        } catch (\Exception $e) {
            // silence
        }
    }
}
