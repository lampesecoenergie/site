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
use Magento\Framework\DB\TransactionFactory;

class InvoiceAndShipProcessor
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var StateLookup
     */
    private $stateLookup;

    /**
     * @var InvoiceProcessor
     */
    private $invoiceProcessor;

    /**
     * @var ShipProcessor
     */
    private $shipProcessor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransactionFactory $transactionFactory,
        StateLookup $stateLookup,
        InvoiceProcessor $invoiceProcessor,
        ShipProcessor $shipProcessor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transactionFactory = $transactionFactory;
        $this->stateLookup = $stateLookup;
        $this->invoiceProcessor = $invoiceProcessor;
        $this->shipProcessor = $shipProcessor;
    }
    /**
     * @param string $orderId
     * @param string $carrierCode
     * @param string $trackingNumber
     *
     * @throws LocalizedException
     */
    public function invoiceAndShip($orderId, $carrierCode, $trackingNumber)
    {
        $transactionSave = $this->transactionFactory->create();

        $invoice = $this->invoiceProcessor->generateInvoice($orderId);
        $transactionSave->addObject($invoice);

        /** @var \Magento\Sales\Api\Data\ShipmentInterface $shipment */
        $shipment = $this->shipProcessor->generateShipment($orderId, $carrierCode, $trackingNumber);
        $transactionSave->addObject($shipment);
        $transactionSave->addObject($shipment->getOrder());
        $transactionSave->save();

        $assignStatus = $this->scopeConfig->getValue('ordermanager/invoiceAndShip/new_status');
        if ($assignStatus) {
            $order = $invoice->getOrder();
            $order->setStatus($assignStatus);
            $order->setState($this->stateLookup->getStateForStatus($assignStatus));
            $order->setIsInProcess(false);
            $transactionSave = $this->transactionFactory->create()->addObject($order);
            $transactionSave->save();
        }

        $this->processEmails($invoice, $shipment);
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface  $invoice
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     */
    public function processEmails(
        \Magento\Sales\Api\Data\InvoiceInterface $invoice,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment
    ) {
        if ($this->scopeConfig->isSetFlag('ordermanager/invoiceAndShip/invoiceemail')) {
            $this->invoiceProcessor->sendInvoiceEmail($invoice);
        }
        if ($this->scopeConfig->isSetFlag('ordermanager/invoiceAndShip/shipmentemail')) {
            $this->shipProcessor->sendShipmentEmail($shipment);
        }
    }
}