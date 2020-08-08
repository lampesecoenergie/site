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
 * Class Truncate
 *
 * @package Ced\Integrator\Controller\Adminhtml\Log
 */
class Truncate extends Action
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
        \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $logs
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileIo = $fileIo;
        $this->logs = $logs;
    }

    public function execute()
    {
        $status = false;
        $collection = $this->logs->create();
        if (isset($collection) and $collection->getSize() > 0) {
            $status = true;
            $collection->walk('delete');
        }

        if ($status) {
            $this->messageManager->addSuccessMessage('Log deleted successfully.');
        } else {
            $this->messageManager->addNoticeMessage('No Log to delete.');
        }

        $this->_redirect('integrator/log/index');
    }
}
