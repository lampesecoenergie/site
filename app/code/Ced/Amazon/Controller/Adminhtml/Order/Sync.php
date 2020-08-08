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

namespace Ced\Amazon\Controller\Adminhtml\Order;

/**
 * Class Sync
 * @package Ced\Amazon\Controller\Adminhtml\Order
 */
class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Magento\Sales\Model\OrderFactory  */
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

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /**
     * Sync constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Ced\Amazon\Helper\Order $order
     * @param \Ced\Amazon\Helper\Shipment $shipment
     * @param \Ced\Amazon\Helper\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Order $order,
        \Ced\Amazon\Helper\Shipment $shipment,
        \Ced\Amazon\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->orderFactory = $orderFactory;
        $this->mporders = $collectionFactory;
        $this->helper = $order;
        $this->shipment = $shipment;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->mporders->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) && !empty($id)) {
                $collection = $this->mporders->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $response = false;
        $message = 'Order(s) synced successfully.';
        if (isset($collection) && $collection->getSize() > 0) {
            $ack = $this->config->getAutoAcknowledgement();

            /** @var \Ced\Amazon\Model\Order $item */
            foreach ($collection as $item) {
                $orderId = $item->getData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID);
                $status = $item->getData(\Ced\Amazon\Model\Order::COLUMN_STATUS);

                // 1. Acknowledge order, if status is not 'Acknowledged'.
                if ($status == \Ced\Amazon\Model\Source\Order\Status::IMPORTED && $ack) {
                    $response = $this->helper->acknowledge(
                        $item->getMagentoOrderId(),
                        $item->getAmazonOrderId()
                    );
                }

                // 2. Create order in magento, if order is not imported and status is 'NotImported'.
                if (empty($orderId)) {
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
//                        if (!isset($mpshipments[$shipmentId]['Feed']['FeedSubmissionId'])) {
                        if (!isset($mpshipments[$shipmentId]['Feed'])) {
                            $this->shipment->create($shipment);
                        }
                    }
                }
            }

            $response = true;
        }

        if ($response) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage('Order(s) sync failed.');
        }

        return $this->_redirect('*/order/index');
    }
}
