<?php
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Shipment;

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
class Pdfshipments extends \Magento\Backend\App\Action
{
    private $redirectUrl = 'sales/order_shipment/index';

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    private $shipmentDocumentFactory;

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
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                                 $context
     * @param \Magento\Ui\Component\MassAction\Filter                             $filter
     * @param \Fooman\PdfCore\Model\PdfFileHandling                               $pdfFileHandling
     * @param \Fooman\PdfCore\Model\PdfRenderer                                   $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                         $shipmentDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        $this->filter = $filter;
        $this->pdfFileHandling = $pdfFileHandling;
        $this->pdfRenderer = $pdfRenderer;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->collectionFactory = $shipmentCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Print selected shipments
     *
     * @return \Magento\Framework\App\ResponseInterface | \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
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
        return $this->_authorization->isAllowed('Magento_Sales::sales_shipment')
            || $this->_authorization->isAllowed('Magento_Sales::shipment');
    }

    /**
     * Print selected shipments
     *
     * @param Collection $collection
     *
     * @return void
     */
    public function processCollection(Collection $collection)
    {
        foreach ($collection->getItems() as $shipment) {
            $document = $this->shipmentDocumentFactory->create(
                ['data' => ['shipment' => $shipment]]
            );

            $this->pdfRenderer->addDocument($document);
        }
    }
}
