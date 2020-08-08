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
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\Order\Pdf\Shipment as PdfShipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintShipments extends \Magento\Backend\App\Action
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
     * @var PdfShipment
     */
    private $pdfShipment;

    /**
     * @var ShipmentCollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * PrintInvoices constructor.
     *
     * @param Context                   $context
     * @param FileFactory               $fileFactory
     * @param DateTime                  $dateTime
     * @param PdfShipment               $pdfShipment
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        DateTime $dateTime,
        PdfShipment $pdfShipment,
        ShipmentCollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
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
            $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

            if ($shipments->getSize() > 0) {
                return $this->fileFactory->create(
                    sprintf('shipment%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                    $this->pdfShipment->getPdf($shipments->getItems())->render(),
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
        return $this->_authorization->isAllowed('Fooman_OrderManager::ship');
    }
}
