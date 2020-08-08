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
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 *
 * @package Ced\Integrator\Controller\Adminhtml\Log
 */
class Delete extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $logs
     */
    public $logs;

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
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $logs
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $logs
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileIo = $fileIo;
        $this->filter = $filter;
        $this->logs = $logs;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->logs->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) and !empty($id)) {
                /** @var \Ced\Integrator\Model\ResourceModel\Log\Collection $collection */
                $collection = $this->logs->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $logsStatus = true;
        if (isset($collection) and $collection->getSize() > 0) {
            $logsStatus = true;
            $collection->walk('delete');
        }

        if ($logsStatus) {
            $this->messageManager->addSuccessMessage('Log deleted successfully.');
        } else {
            $this->messageManager->addErrorMessage('Log delete failed.');
        }

        $this->_redirect('integrator/log/index');
    }
}
