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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service\Order\Shipment;

use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Shipment as AmazonOrderShipmentService;
use Ced\Amazon\Model\ResourceModel\Order\CollectionFactory as AmazonOrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as MagentoShipmentCollectionFactory;

class Sync
{
    /** @var AmazonOrderShipmentService  */
    public $amazonOrderShipmentService;

    /** @var AmazonOrderCollectionFactory  */
    public $amazonOrderCollectionFactory;

    /** @var MagentoShipmentCollectionFactory  */
    public $magentoShipmentCollectionFactory;

    /** @var Logger  */
    public $logger;

    public function __construct(
        MagentoShipmentCollectionFactory $magentoShipmentCollectionFactory,
        AmazonOrderShipmentService $amazonOrderShipmentService,
        AmazonOrderCollectionFactory $amazonOrderCollectionFactory,
        Logger $logger
    ) {
        $this->amazonOrderShipmentService = $amazonOrderShipmentService;
        $this->magentoShipmentCollectionFactory = $magentoShipmentCollectionFactory;
        $this->amazonOrderCollectionFactory = $amazonOrderCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Process the given ids
     * @param mixed $ids
     * @return boolean
     */
    public function process($ids)
    {
        try {
            $orders = $this->amazonOrderCollectionFactory->create()
                ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_ID, ["in" => $ids])
                ->addFieldToFilter(
                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                    [["notnull" => true], ['neq' => '']]
                );

            /** @var \Ced\Amazon\Model\Order $order */
            foreach ($orders as $order) {
                $amazonShipmentList = $this->amazonOrderShipmentService->get($order->getId(), null, $order);
                $shipments = $this->magentoShipmentCollectionFactory->create()
                    ->addFieldToFilter(
                        "order_id",
                        ["eq" => $order->getData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID)]
                    );

                if ($shipments->getSize() > 0) {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    foreach ($shipments as $shipment) {
                        $shipmentId = $shipment->getId();
                        if (!isset($amazonShipmentList[$shipmentId]['Feed'])) {
                            $this->amazonOrderShipmentService->create($shipment);
                        }
                    }
                }
            }

            $status = true;
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                " Error in Bulk Order Shipment sync.",
                [
                    'ids' => $ids,
                    'trace' => $e->getTraceAsString()
                ]
            );
        }
        return $status;
    }
}
