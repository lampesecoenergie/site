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

use Fooman\OrderManager\Model\InvoiceAndShipProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class InvoiceAndShip extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var InvoiceAndShipProcessor
     */
    protected $invoiceAndShipProcessor;

    protected $processedOrderIds;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param InvoiceAndShipProcessor $invoiceAndShipProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        InvoiceAndShipProcessor $invoiceAndShipProcessor
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->invoiceAndShipProcessor = $invoiceAndShipProcessor;
    }

    /**
     * Invoice and ships selected orders
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $trackingCarriers = $this->getRequest()->getParam('tracking_carrier');
        $trackingNumbers = $this->getRequest()->getParam('tracking_number');
        $countSuccess = 0;
        $this->processedOrderIds = [];

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect;
        }

        foreach ($collection->getItems() as $order) {
            try {
                if (!$order->canShip() || !$order->canInvoice()) {
                    continue;
                }

                $this->invoiceAndShipProcessor->invoiceAndShip(
                    $order->getEntityId(),
                    isset($trackingCarriers[$order->getId()]) ? $trackingCarriers[$order->getId()] : '',
                    isset($trackingNumbers[$order->getId()]) ? $trackingNumbers[$order->getId()] : ''
                );
                $this->processedOrderIds[] = $order->getId();
                $countSuccess++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf('#%s: %s', $order->getIncrementId(), $e->getMessage()));
            }
        }

        $countFailure = $collection->getSize() - $countSuccess;

        if ($countFailure && $countSuccess) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not invoiced or shipped.', $countFailure));
        } elseif ($countFailure) {
            $this->messageManager->addErrorMessage(__('No order(s) were invoiced or shipped.'));
        }

        if ($countSuccess) {
            $this->messageManager->addSuccessMessage(__('%1 order(s) were invoiced and shipped.', $countSuccess));
        }

        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * Return component referrer url
     *
     * @return null|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: 'sales/*/';
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fooman_OrderManager::invoiceAndShip');
    }
}
