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

namespace Ced\Amazon\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;

/**
 * Class Create
 * @package Ced\Amazon\Controller\Adminhtml\Order\Shipment
 */
class Create extends Action
{
    /**
     * @var \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory
     */
    public $mporders;

    public $orders;

    /** @var \Ced\Amazon\Helper\Shipment */
    public $shipment;

    /**
     * Create constructor.
     * @param Action\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $mporderCollectionFactory
     * @param \Ced\Amazon\Helper\Shipment $shipment
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $mporderCollectionFactory,
        \Ced\Amazon\Helper\Shipment $shipment
    ) {
        parent::__construct($context);
        $this->orders = $orderCollectionFactory;
        $this->mporders = $mporderCollectionFactory;
        $this->shipment = $shipment;
    }

    public function execute()
    {
        $synced = 0;
        /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $mporders */
        $mporders = $this->mporders->create()
            ->addFieldToFilter(
                \Ced\Amazon\Model\Order::COLUMN_STATUS,
                ['neq' => \Ced\Amazon\Model\Source\Order\Status::SHIPPED]
            );

        $mporderIds = $mporders->getColumnValues(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID);

        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orders */
        $orders =  $this->orders->create()
            ->addFieldToFilter('entity_id', ['in' => $mporderIds]);

        if (isset($orders) && $orders->getSize() > 0) {
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($orders as $order) {
                /** @var \Magento\Framework\DataObject|null $mporder */
                $mporder = $mporders->getItemByColumnValue(
                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                    $order->getId()
                );

                /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection|false $shipments */
                $shipments = $order->getShipmentsCollection();
                if (!empty($shipments)) {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    foreach ($shipments as $shipment) {
                        /** @var array $mpshipment */
                        $mpshipment = $this->shipment->get(
                            $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ID),
                            $shipment->getId(),
                            $mporder
                        );

                        if (empty($mpshipment)) {
                            $synced++;
                            $this->shipment->create($shipment);
                        }
                    }
                }
            }
        }

        $this->messageManager->addSuccessMessage("{$synced} shipment(s) synced successfully.");

        /** @var \Magento\Framework\Controller\Result\Redirect $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $response->setPath('*/*/listorder');
        return $response;
    }
}
