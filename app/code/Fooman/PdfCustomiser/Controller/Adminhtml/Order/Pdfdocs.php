<?php
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Order;

use Magento\Framework\Data\Collection;
use Magento\Framework\Controller\ResultFactory;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Pdfdocs extends \Magento\Backend\App\Action
{
    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    private $invoiceDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    private $creditmemoDocumentFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Fooman\PdfCore\Model\PdfFileHandling
     */
    private $pdfFileHandling;

    /**
     * @var \Fooman\PdfCore\Model\PdfRenderer
     */
    private $pdfRenderer;

    /**
     * @var string
     */
    private $redirectUrl = 'sales/order/index';

    /**
     * @var \Fooman\PdfCustomiser\Block\OrderFactory
     */
    private $orderDocumentFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Fooman\PdfCore\Model\PdfFileHandling                      $pdfFileHandling
     * @param \Fooman\PdfCore\Model\PdfRenderer                          $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\OrderFactory                   $orderDocumentFactory
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory                 $invoiceDocumentFactory
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                $shipmentDocumentFactory
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory              $creditmemoDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->filter = $filter;
        $this->pdfFileHandling = $pdfFileHandling;
        $this->pdfRenderer = $pdfRenderer;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->orderDocumentFactory = $orderDocumentFactory;
        $this->collectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Print selected documents
     *
     * @return \Magento\Framework\App\ResponseInterface | \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $this->processCollection($collection);

        if ($this->pdfRenderer->hasPrintContent()) {
            return $this->pdfFileHandling->sendPdfFile($this->pdfRenderer);
        }

        $this->messageManager->addErrorMessage(__('Nothing to print'));
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->redirectUrl);
    }

    /**
     * @return bool
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order')
            && $this->_authorization->isAllowed('Magento_Sales::sales_invoice')
            && ($this->_authorization->isAllowed('Magento_Sales::shipment')
                || $this->_authorization->isAllowed('Magento_Sales::sales_shipment'))
            && $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Print selected orders, invoices, creditmemo and shipments for selected orders
     *
     * @param Collection $collection
     *
     * @return void
     */
    public function processCollection(Collection $collection)
    {
        foreach ($collection->getItems() as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            /** @var \Fooman\PdfCustomiser\Block\Order $document */
            $document = $this->orderDocumentFactory->create(
                ['data' => ['order' => $order]]
            );

            $this->pdfRenderer->addDocument($document);

            $invoices = $order->getInvoiceCollection();
            if ($invoices) {
                foreach ($invoices as $invoice) {
                    $document = $this->invoiceDocumentFactory->create(
                        ['data' => ['invoice' => $invoice]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }

            $shipments = $order->getShipmentsCollection();
            if ($shipments) {
                foreach ($shipments as $shipment) {
                    $document = $this->shipmentDocumentFactory->create(
                        ['data' => ['shipment' => $shipment]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }

            $creditmemos = $order->getCreditmemosCollection();
            if ($creditmemos) {
                foreach ($creditmemos as $creditmemo) {
                    $document = $this->creditmemoDocumentFactory->create(
                        ['data' => ['creditmemo' => $creditmemo]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }
        }
    }
}
