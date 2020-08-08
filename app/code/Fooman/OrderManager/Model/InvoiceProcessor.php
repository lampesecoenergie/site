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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\TransactionFactory;
use Psr\Log\LoggerInterface;
use Fooman\OrderManager\Model\Source\EmailingOptions;

class InvoiceProcessor
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
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * can't use @see \Magento\Sales\Api\InvoiceManagementInterface as missing prepareInvoice()
     * @var InvoiceService
     */
    private $invoiceManagement;

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

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        InvoiceSender $invoiceSender,
        TransactionFactory $transactionFactory,
        InvoiceService $invoiceManagement,
        LoggerInterface $logger,
        StateLookup $stateLookup
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceManagement = $invoiceManagement;
        $this->logger = $logger;
        $this->stateLookup = $stateLookup;
    }

    /**
     * @param string $orderId
     *
     * @param bool   $sendEmails
     *
     * @throws LocalizedException
     */
    public function invoice($orderId, $sendEmails = false)
    {
        $invoice = $this->generateInvoice($orderId);

        $transactionSave = $this->transactionFactory->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();

        $assignStatus = $this->scopeConfig->getValue('ordermanager/invoice/new_status');
        if ($assignStatus) {
            $order = $invoice->getOrder();
            $order->setStatus($assignStatus);
            $order->setState($this->stateLookup->getStateForStatus($assignStatus));
            $order->setIsInProcess(false);
            $transactionSave = $this->transactionFactory->create()->addObject($order);
            $transactionSave->save();
        }

        $this->processEmails($invoice, $sendEmails);
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws LocalizedException
     */
    public function generateInvoice($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if (!$order->canInvoice()) {
            throw new LocalizedException(
                __('The order does not allow an invoice to be created.')
            );
        }

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceManagement->prepareInvoice($order);

        if (!$invoice) {
            throw new LocalizedException(__('We can\'t save the invoice right now.'));
        }

        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);

        return $invoice;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @param bool                                     $sendEmails
     */
    public function processEmails(\Magento\Sales\Api\Data\InvoiceInterface $invoice, $sendEmails = false)
    {
        if ($sendEmails
            || $this->scopeConfig->getValue('ordermanager/invoice/email') == EmailingOptions::SEND_EMAIL_YES) {
            $this->sendInvoiceEmail($invoice);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     */
    public function sendInvoiceEmail(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        try {
            $this->invoiceSender->send($invoice);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}