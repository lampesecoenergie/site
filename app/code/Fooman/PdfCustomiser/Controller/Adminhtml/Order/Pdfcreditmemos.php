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
class Pdfcreditmemos extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

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
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Fooman\PdfCore\Model\PdfFileHandling                      $pdfFileHandling
     * @param \Fooman\PdfCore\Model\PdfRenderer                          $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory              $creditmemoDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->collectionFactory = $orderCollectionFactory;
        $this->filter = $filter;
        $this->pdfFileHandling = $pdfFileHandling;
        $this->pdfRenderer = $pdfRenderer;
        parent::__construct($context);
    }

    /**
     * Print selected credit memos
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
     * Print selected creditmemos
     *
     * @param Collection $collection
     *
     * @return void
     */
    public function processCollection(Collection $collection)
    {
        foreach ($collection->getItems() as $order) {
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
