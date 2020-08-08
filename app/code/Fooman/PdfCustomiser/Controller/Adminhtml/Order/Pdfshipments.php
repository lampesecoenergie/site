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
class Pdfshipments extends \Magento\Backend\App\Action
{

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Block\OrderShipmentFactory
     */
    private $orderShipmentDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Model\ControllerConfig
     */
    private $config;

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
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Fooman\PdfCore\Model\PdfFileHandling                      $pdfFileHandling
     * @param \Fooman\PdfCore\Model\PdfRenderer                          $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                $shipmentDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Fooman\PdfCustomiser\Block\OrderShipmentFactory           $orderShipmentDocumentFactory
     * @param \Fooman\PdfCustomiser\Model\ControllerConfig               $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Fooman\PdfCustomiser\Block\OrderShipmentFactory $orderShipmentDocumentFactory,
        \Fooman\PdfCustomiser\Model\ControllerConfig $config
    ) {
        $this->filter = $filter;
        $this->pdfFileHandling = $pdfFileHandling;
        $this->pdfRenderer = $pdfRenderer;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderShipmentDocumentFactory = $orderShipmentDocumentFactory;
        $this->collectionFactory = $orderCollectionFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Print selected shipments
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
        return (
            $this->_authorization->isAllowed('Magento_Sales::shipment')
            || $this->_authorization->isAllowed('Magento_Sales::sales_shipment')
        );
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
        foreach ($collection->getItems() as $order) {
            if ($this->config->shouldPrintOrderAsPackingSlip()) {
                $document = $this->orderShipmentDocumentFactory->create(
                    ['data' => ['order' => $order]]
                );

                $this->pdfRenderer->addDocument($document);
            } else {
                $shipments = $order->getShipmentsCollection();
                if ($shipments) {
                    foreach ($shipments as $shipment) {
                        $document = $this->shipmentDocumentFactory->create(
                            ['data' => ['shipment' => $shipment]]
                        );

                        $this->pdfRenderer->addDocument($document);
                    }
                }
            }
        }
    }
}
