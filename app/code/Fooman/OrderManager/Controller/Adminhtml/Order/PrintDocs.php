<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\Order\Pdf\Shipment as PdfShipment;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintDocs extends \Magento\Backend\App\Action
{

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var PdfInvoice
     */
    private $pdfInvoice;

    /**
     * @var InvoiceCollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var PdfShipment
     */
    private $pdfShipment;

    /**
     * @var ShipmentCollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * PrintDocs constructor.
     *
     * @param Context                   $context
     * @param FileFactory               $fileFactory
     * @param DateTime                  $dateTime
     * @param PdfInvoice                $pdfInvoice
     * @param InvoiceCollectionFactory  $invoiceCollectionFactory
     * @param PdfShipment               $pdfShipment
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        DateTime $dateTime,
        PdfInvoice $pdfInvoice,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        PdfShipment $pdfShipment,
        ShipmentCollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->pdfShipment = $pdfShipment;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $orderIds = $this->_request->getParam('printIds');
        if ($orderIds) {
            $invoices = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
            $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

            if ($invoices->getSize() > 0) {
                $pdf = $this->pdfInvoice->getPdf($invoices->getItems());
                $shipmentsPdf = $this->pdfShipment->getPdf($shipments->getItems());

                $pdf->pages = array_merge($pdf->pages, $shipmentsPdf->pages);

                return $this->fileFactory->create(
                    sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/index');
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fooman_OrderManager::invoiceAndShip');
    }
}
