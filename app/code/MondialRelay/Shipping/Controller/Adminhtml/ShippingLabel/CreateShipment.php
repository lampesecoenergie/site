<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ShippingLabel;

use MondialRelay\Shipping\Model\Config\Source\Status;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Exception;

/**
 * Class CreateShipment
 */
class CreateShipment extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var ConvertOrder $convertOrder
     */
    protected $convertOrder;

    /**
     * @var ShipmentNotifier $shipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @param Context $context
     * @param LabelGenerator $labelGenerator
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     */
    public function __construct(
        Context $context,
        LabelGenerator $labelGenerator,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier
    ) {
        parent::__construct($context);

        $this->labelGenerator     = $labelGenerator;
        $this->orderRepository    = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->convertOrder       = $convertOrder;
        $this->shipmentNotifier   = $shipmentNotifier;
    }

    /**
     * Create Shipment and Shipping Label
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        if (!$order->canShip()) {
            $this->messageManager->addErrorMessage(
                __('You can\'t create the Shipment of this order.')
            );
        }

        $shipment = $this->convertOrder->toShipment($order);

        /** @var Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qty);

            $shipment->addItem($shipmentItem);
        }

        try {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            $this->labelGenerator->create($shipment, $this->getRequest());
            $this->shipmentRepository->save($shipment);
            $this->orderRepository->save($order);

            $this->shipmentNotifier->notify($shipment);

            $this->messageManager->addSuccessMessage(__('The shipment has been created.'));
            $this->messageManager->addSuccessMessage(__('You created the shipping label.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
