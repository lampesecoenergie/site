<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Framework\DB\TransactionFactory;
use Psr\Log\LoggerInterface;
use Fooman\OrderManager\Model\Source\EmailingOptions;

class ShipProcessor
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentSender
     */
    private $shipmentSender;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var StateLookup
     */
    private $stateLookup;

    /**
     * @var CarrierTitleLookup
     */
    private $carrierTitleLookup;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        ShipmentSender $shipmentSender,
        TransactionFactory $transactionFactory,
        ShipmentFactory $shipmentFactory,
        LoggerInterface $logger,
        StateLookup $stateLookup,
        CarrierTitleLookup $carrierTitleLookup
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->shipmentSender = $shipmentSender;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->logger = $logger;
        $this->stateLookup = $stateLookup;
        $this->carrierTitleLookup = $carrierTitleLookup;
    }

    /**
     * @param string $orderId
     * @param string $carrierCode
     * @param string $trackingNumber
     *
     * @param bool   $sendEmails
     *
     * @throws LocalizedException
     */
    public function ship($orderId, $carrierCode, $trackingNumber, $sendEmails = false)
    {
        /** @var \Magento\Sales\Api\Data\ShipmentInterface $shipment */
        $shipment = $this->generateShipment($orderId, $carrierCode, $trackingNumber);

        $transactionSave = $this->transactionFactory->create()
                                                    ->addObject($shipment)
                                                    ->addObject($shipment->getOrder());

        $transactionSave->save();

        $assignStatus = $this->scopeConfig->getValue('ordermanager/ship/new_status');
        if ($assignStatus) {
            $order = $shipment->getOrder();
            $order->setStatus($assignStatus);
            $order->setState($this->stateLookup->getStateForStatus($assignStatus));
            $order->setIsInProcess(false);
            $transactionSave = $this->transactionFactory->create()->addObject($order);
            $transactionSave->save();
        }

        $this->processEmails($shipment, $sendEmails);
    }

    /**
     * @param $orderId
     * @param $carrierCode
     * @param $trackingNumber
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     * @throws LocalizedException
     */
    public function generateShipment($orderId, $carrierCode, $trackingNumber)
    {
        $order = $this->orderRepository->get($orderId);

        if (!$order->canShip()) {
            throw new LocalizedException(
                __('The order does not allow a shipment to be created.')
            );
        }

        //We want to ship all available items
        $itemsToShip = [];
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getQtyToShip() > 0) {
                $itemsToShip[$orderItem->getItemId()] = $orderItem->getQtyToShip();
            }
        }

        if (empty($carrierCode) || empty($trackingNumber)) {
            $shipment = $this->shipmentFactory->create($order, $itemsToShip);
        } else {
            $shipment = $this->shipmentFactory->create(
                $order,
                $itemsToShip,
                [
                    [
                        'carrier_code' => $carrierCode,
                        'number' => $trackingNumber,
                        'title' => $this->getTitleFromCarrierCode($carrierCode),
                    ]
                ]
            );
        }

        if (!$shipment) {
            throw new LocalizedException(__('We can\'t save the shipment right now.'));
        }

        $shipment->register();
        $shipment->getOrder()->setCustomerNoteNotify(true);
        $shipment->getOrder()->setIsInProcess(true);

        return $shipment;
    }

    /**
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param bool                                      $sendEmails
     */
    public function processEmails(\Magento\Sales\Api\Data\ShipmentInterface $shipment, $sendEmails = false)
    {
        if ($sendEmails ||
            $this->scopeConfig->getValue('ordermanager/ship/email') == EmailingOptions::SEND_EMAIL_YES) {
            $this->sendShipmentEmail($shipment);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     */
    public function sendShipmentEmail(\Magento\Sales\Api\Data\ShipmentInterface $shipment)
    {
        try {
            $this->shipmentSender->send($shipment);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    public function getTitleFromCarrierCode($code)
    {
        return $this->carrierTitleLookup->getTitleFromCarrierCode($code);
    }
}
