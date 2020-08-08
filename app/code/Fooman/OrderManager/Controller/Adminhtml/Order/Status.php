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

use Fooman\OrderManager\Model\StatusProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Status extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var StatusProcessor
     */
    protected $statusProcessor;

    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param StatusProcessor $statusProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        StatusProcessor $statusProcessor
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->statusProcessor = $statusProcessor;
    }

    /**
     * Updates status on selected orders
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $newStatus = $this->getRequest()->getParam('new_status');
        $countSuccess = 0;

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
                $this->statusProcessor->setStatus($order->getEntityId(), $newStatus);
                $countSuccess++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(sprintf('#%s: %s', $order->getIncrementId(), $e->getMessage()));
            }
        }

        $countFailure = $collection->getSize() - $countSuccess;

        if ($countFailure && $countSuccess) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were updated.', $countFailure));
        } elseif ($countFailure) {
            $this->messageManager->addErrorMessage(__('No order(s) were updated.'));
        }

        if ($countSuccess) {
            $this->messageManager->addSuccessMessage(__('%1 order(s) were updated.', $countSuccess));
        }

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
        return $this->_authorization->isAllowed('Fooman_OrderManager::status');
    }
}
