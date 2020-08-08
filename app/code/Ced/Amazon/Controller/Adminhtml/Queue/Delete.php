<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Delete
 * @package Ced\Amazon\Controller\Adminhtml\Queue
 */
class Delete extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var QueueCollectionFactory
     */
    public $queue;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $fileIo;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param File $fileIo
     * @param Filter $filter
     * @param QueueCollectionFactory $queue
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        File $fileIo,
        Filter $filter,
        QueueCollectionFactory $queue
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileIo = $fileIo;
        $this->filter = $filter;
        $this->queue = $queue;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->queue->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) && !empty($id)) {
                $collection = $this->queue->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $status = false;
        if (isset($collection) && $collection->getSize() > 0) {
            $status = true;
            $collection->walk("delete");
        }

        if ($status) {
            $this->messageManager->addSuccessMessage('Queue entries deleted successfully.');
        } else {
            $this->messageManager->addErrorMessage('Queue entries delete failed.');
        }

        return $this->_redirect('amazon/queue');
    }
}
