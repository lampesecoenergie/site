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

use Fooman\OrderManager\Model\ShipProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Ship extends \Magento\Backend\App\Action
{
    
    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @var ShipProcessor
     */
    protected $shipProcessor;

    protected $shippedOrderIds;

    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ShipProcessor $shipProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ShipProcessor $shipProcessor
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->shipProcessor = $shipProcessor;
    }

    /**
     * Ships selected orders
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $trackingCarriers = $this->getRequest()->getParam('tracking_carrier');
        $trackingNumbers = $this->getRequest()->getParam('tracking_number');
        $sendEmails = $this->getRequest()->getParam('email', false);
        $countSuccess = 0;
        $this->shippedOrderIds = [];

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
                if (!$order->canShip()) {
                    continue;
                }

                $this->shipProcessor->ship(
                    $order->getEntityId(),
                    isset($trackingCarriers[$order->getId()]) ? $trackingCarriers[$order->getId()] : '',
                    isset($trackingNumbers[$order->getId()]) ? $trackingNumbers[$order->getId()] : '',
                    $sendEmails
                );
                $this->shippedOrderIds[] = $order->getId();
                $countSuccess++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf('#%s: %s', $order->getIncrementId(), $e->getMessage()));
            }
        }

        $countFailure = $collection->getSize() - $countSuccess;

        if ($countFailure && $countSuccess) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not shipped.', $countFailure));
        } elseif ($countFailure) {
            $this->messageManager->addErrorMessage(__('No order(s) were shipped.'));
        }

        if ($countSuccess) {
            $this->messageManager->addSuccessMessage(__('%1 order(s) were shipped.', $countSuccess));
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
        return $this->_authorization->isAllowed('Fooman_OrderManager::ship');
    }
}
