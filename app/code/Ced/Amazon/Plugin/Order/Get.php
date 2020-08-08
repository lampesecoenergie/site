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

namespace Ced\Amazon\Plugin\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Ced\Amazon\Api\OrderRepositoryInterface;

class Get
{
    /** @var OrderRepositoryInterface  */
    public $orderRepository;

    /** @var OrderExtensionFactory  */
    public $orderExtensionFactory;

    public function __construct(
        OrderExtensionFactory $extensionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderExtensionFactory = $extensionFactory;
    }

    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getMarketplaceOrderIdAttribute($resultOrder);

        return $resultOrder;
    }

    private function getMarketplaceOrderIdAttribute(\Magento\Sales\Api\Data\OrderInterface $order)
    {

        try {
            $marketplaceOrder = $this->orderRepository->getByOrderId($order->getEntityId());
            $marketplaceOrderIdAttributeValue = $marketplaceOrder->getAmazonOrderId();
            $marketplaceOrderPlaceDateAttributeValue = $marketplaceOrder->getOrderPlaceDate();
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setAmazonOrderId($marketplaceOrderIdAttributeValue);
        $orderExtension->setAmazonOrderPlaceDate($marketplaceOrderPlaceDateAttributeValue);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
}
