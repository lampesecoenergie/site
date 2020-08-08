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

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Exception;

/**
 * Class InlineEdit
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var JsonFactory $jsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);

        $this->orderRepository    = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->jsonFactory        = $jsonFactory;
    }

    /**
     * Save order or shipping data
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $orderId) {
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);

            try {
                foreach ($postItems[$orderId] as $attribute => $value) {
                    if ($attribute === ShipmentInterface::SHIPMENT_STATUS) {
                        $shipments = $order->getShipmentsCollection();
                        /** @var Shipment $shipment */
                        foreach ($shipments as $shipment) {
                            $shipment->setShipmentStatus($value);
                            $this->shipmentRepository->save($shipment);
                        }
                    }

                    if ($attribute === OrderInterface::WEIGHT) {
                        if (!$order->hasShipments()) {
                            $order->setWeight($value);
                            $this->orderRepository->save($order);
                        }
                    }

                    if ($attribute === ShippingDataInterface::MONDIAL_RELAY_PACKAGING_WEIGHT) {
                        if (!$order->hasShipments()) {
                            $order->setData(ShippingDataInterface::MONDIAL_RELAY_PACKAGING_WEIGHT, $value);
                            $this->orderRepository->save($order);
                        }
                    }
                }
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithOrderId($order, $e->getMessage());
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => $error
        ]);
    }

    /**
     * Add price title to error message
     *
     * @param OrderInterface $order
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithOrderId(OrderInterface $order, $errorText)
    {
        return '[' . $order->getIncrementId() . '] ' . $errorText;
    }
}
