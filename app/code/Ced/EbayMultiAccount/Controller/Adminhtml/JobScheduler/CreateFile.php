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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler;

/**
 * Class CreateFile
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class CreateFile extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /** @var \Ced\EbayMultiAccount\Helper\FileUpload */
    public $fileUploadHelper;

    /** @var \Ced\EbayMultiAccount\Model\JobScheduler */
    public $schedulerCollection;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * CreateFile constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\Collection $schedulerResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\Collection $schedulerResource
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->schedulerCollection = $schedulerResource;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Ced\EbayMultiAccount\Model\JobScheduler */
        $schedulerCollection = $this->schedulerCollection
            ->addFieldToFilter('cron_status', 'scheduled');
        if ($schedulerCollection->getSize() > 0) {
            $schedulerChunks = array_chunk($schedulerCollection->getColumnValues('id'), 1);
            $this->_session->setSchedulerIds($schedulerChunks);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_EbayMultiAccount::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Creating File'));
            return $resultPage;
        } elseif ($schedulerCollection->getSize() <= 0) {
            $this->messageManager->addErrorMessage(__('Job Not Scheduled For Scheduler.'));
        }
        $result = $this->resultRedirectFactory->create();
        $result->setPath('ebaymultiaccount/jobScheduler/index');
        return $result;
    }
}
