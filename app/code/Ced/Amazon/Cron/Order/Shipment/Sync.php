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

namespace Ced\Amazon\Cron\Order\Shipment;

class Sync
{
    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Magento\Sales\Model\OrderFactory */
    public $orderFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory
     */
    public $mporders;

    /**
     * @var \Ced\Amazon\Helper\Order
     */
    public $helper;

    /** @var \Ced\Amazon\Helper\Shipment */
    public $shipment;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Order $order,
        \Ced\Amazon\Helper\Shipment $shipment,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Config $config
    ) {
        $this->orderFactory = $orderFactory;
        $this->mporders = $collectionFactory;
        $this->helper = $order;
        $this->shipment = $shipment;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $ack = $this->config->getAutoAcknowledgement();
            $import = false;

            if ($this->config->getOrderImport()) {
                $now = date("Y-m-d");
                $start = date('Y-m-d', strtotime('-5 days', strtotime($now)));

                $collection = $this->mporders->create()
                    ->addFieldToFilter(
                        \Ced\Amazon\Model\Order::COLUMN_STATUS,
                        [
                            'in' => [
                                    \Ced\Amazon\Model\Source\Order\Status::IMPORTED,
                                    \Ced\Amazon\Model\Source\Order\Status::ACKNOWLEDGED,
                                    \Ced\Amazon\Model\Source\Order\Status::UNSHIPPED,
                                    \Ced\Amazon\Model\Source\Order\Status::PARTIALLY_SHIPPED,
                                    \Ced\Amazon\Model\Source\Order\Status::UNSHIPPED,
                                ]
                        ]
                    )
                    ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_PO_DATE, ['from' => $start, 'to' => $now]);

                if (isset($collection) && $collection->getSize() > 0) {
                    /** @var \Ced\Amazon\Model\Order $item */
                    foreach ($collection as $item) {
                        $orderId = $item->getData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID);
                        $status = $item->getData(\Ced\Amazon\Model\Order::COLUMN_STATUS);

                        // 1. Acknowledge order, if status is not 'Acknowledged'.
                        if ($ack && $status == \Ced\Amazon\Model\Source\Order\Status::IMPORTED &&
                            $this->config->getAutoAcknowledgement()) {
                            $response = $this->helper->acknowledge(
                                $item->getMagentoOrderId(),
                                $item->getAmazonOrderId()
                            );
                        }

                        // 2. Create order in magento, if order is not imported and status is 'NotImported'.
                        if (empty($orderId) && $import) {
                            $this->helper->import([$item->getAccountId()], $item->getAmazonOrderId());
                        }

                        // 3. Create Shipment on Amazon if order shipment is not created and status is in
                        // ['PartiallyShipped', 'Imported', 'Acknowledged', 'Unshipped']
                        if (!empty($orderId) && in_array($status, [
                                \Ced\Amazon\Model\Source\Order\Status::IMPORTED,
                                \Ced\Amazon\Model\Source\Order\Status::ACKNOWLEDGED,
                                \Ced\Amazon\Model\Source\Order\Status::PARTIALLY_SHIPPED,
                                \Ced\Amazon\Model\Source\Order\Status::UNSHIPPED,
                            ])
                        ) {
                            // 3.1: Sync order
                            // 3.2: Check Shipments in Magento
                            // 3.3: Create Shipments if not created in Amazon.
                            $mpshipments = $this->shipment->get($item->getId(), null, $item);
                            $order = $this->orderFactory->create()->load($orderId);
                            /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipments */
                            $shipments = $order->getShipmentsCollection();

                            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                            foreach ($shipments as $shipment) {
                                $shipmentId = $shipment->getId();
                                //if (!isset($mpshipments[$shipmentId]['Feed']['FeedSubmissionId'])) {
                                if (!isset($mpshipments[$shipmentId]['Feed'])) {
                                    $this->shipment->create($shipment);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }
    }
}
