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
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintInvoices extends \Magento\Backend\App\Action
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
     * PrintInvoices constructor.
     *
     * @param Context                  $context
     * @param FileFactory              $fileFactory
     * @param DateTime                 $dateTime
     * @param PdfInvoice               $pdfInvoice
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        DateTime $dateTime,
        PdfInvoice $pdfInvoice,
        InvoiceCollectionFactory $invoiceCollectionFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
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

            if ($invoices->getSize() > 0) {
                return $this->fileFactory->create(
                    sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                    $this->pdfInvoice->getPdf($invoices->getItems())->render(),
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
        return $this->_authorization->isAllowed('Fooman_OrderManager::invoice');
    }
}
