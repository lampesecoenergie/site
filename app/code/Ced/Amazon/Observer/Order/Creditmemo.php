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

use Ced\Amazon\Helper\Config;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Product\Inventory;
use Ced\Amazon\Model\Profile\Product;
use Magento\Framework\Event\ObserverInterface;

class Creditmemo implements ObserverInterface
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
     * @param Config $config
     * @param Product $product
     */
    public function __construct(
        Logger $logger,
        Inventory $inventory,
        Config $config,
        Product $product
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->inventory = $inventory;
        $this->product = $product;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->config->getInventorySync()) {
                /** @var \Magento\Framework\Event  $event */
                $event = $observer->getEvent();

                if ($event->hasData('creditmemo')) {
                    /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
                    $creditmemo = $event->getData('creditmemo');
                    $productIds = [];
                    foreach ($creditmemo->getAllItems() as $item) {
                        if ($item->getBackToStock() &&
                            $this->product->isMarketplaceProduct($item->getProductId(), $creditmemo->getStoreId())) {
                            $productIds[$item->getProductId()] = $item->getProductId();
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
