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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Observer\Product;

use Ced\Amazon\Model\Profile\Product;
use Magento\Framework\Event\ObserverInterface;
use Ced\Amazon\Helper\Product\Price;
use Ced\Amazon\Helper\Config;
use Ced\Amazon\Helper\Logger;

class Save implements ObserverInterface
{
    /**
     * Amazon Logger
     * @var \Ced\Amazon\Helper\Logger
     */
    private $logger;

    /** @var Config */
    private $config;

    /** @var Price */
    private $price;

    /** @var Product  */
    private $product;

    /**
     * Change constructor.
     * @param Logger $logger
     * @param Price $price
     * @param Config $config
     * @param Product $product
     */
    public function __construct(
        Logger $logger,
        Price $price,
        Config $config,
        Product $product
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->price = $price;
        $this->product = $product;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->config->getPriceSync()) {
                /** @var \Magento\Framework\Event  $event */
                $event = $observer->getEvent();
                if ($event->hasData('product')) {
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $event->getData('product');
                    $productId = $product->getId();
                    if ($this->product->isMarketplaceProduct($productId, $product->getStoreId()) &&
                        $product->getData('price') != $product->getOrigData('price')) {
                        $this->price->update([$productId], true, \Ced\Amazon\Model\Source\Queue\Priorty::HIGH);
                    }
                }
            }
        } catch (\Exception $e) {
            // silence
        }
    }
}
