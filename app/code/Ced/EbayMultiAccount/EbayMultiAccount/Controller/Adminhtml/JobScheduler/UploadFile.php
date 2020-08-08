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
 * Class UploadFile
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class UploadFile extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /** @var \Ced\EbayMultiAccount\Helper\FileUpload */
    public $fileUploadHelper;

    /** @var \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory  */
    public $schedulerCollection;

    /** @var \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler  */
    public $schedulerModel;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * UploadFile constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection
     * @param \Ced\EbayMultiAccount\Model\JobScheduler $schedulerResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\EbayMultiAccount\Model\JobScheduler $schedulerResource,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerModel = $schedulerResource;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * Product list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $schedulerChunks = array();
        /** @var \Ced\EbayMultiAccount\Model\JobScheduler $schedulerCollection */
        $schedulerModel = $this->schedulerModel;
        $accounts = $this->multiAccountHelper->getAllAccounts();
        foreach ($accounts as $account) {
            $accountId = $account->getId();
            $addItemSchedulerCollection = $this->schedulerCollection->create()
                ->addFieldToFilter('cron_status', 'file_created')
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('scheduler_type', $schedulerModel::ADDITEM);
            if ($addItemSchedulerCollection->getSize() > 0) {
                $schedulerChunks[] = $addItemSchedulerCollection->getColumnValues('id');
            }

            $fixedPriceItemSchedulerCollection = $this->schedulerCollection->create()
                ->addFieldToFilter('cron_status', 'file_created')
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('scheduler_type', $schedulerModel::ADDFIXEDPRICEITEM);
            if ($fixedPriceItemSchedulerCollection->getSize() > 0) {
                $schedulerChunks[] = $fixedPriceItemSchedulerCollection->getColumnValues('id');
            }

            $reviseItemSchedulerCollection = $this->schedulerCollection->create()
                ->addFieldToFilter('cron_status', 'file_created')
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('scheduler_type', $schedulerModel::REVISEITEM);
            if ($reviseItemSchedulerCollection->getSize() > 0) {
                $schedulerChunks[] = $reviseItemSchedulerCollection->getColumnValues('id');
            }

            $reviseInvSchedulerCollection = $this->schedulerCollection->create()
                ->addFieldToFilter('cron_status', 'file_created')
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('scheduler_type', $schedulerModel::REVISEINVENTORYSTATUS);
            if ($reviseInvSchedulerCollection->getSize() > 0) {
                $schedulerChunks[] = $reviseInvSchedulerCollection->getColumnValues('id');
            }
        }
        if (count($schedulerChunks) > 0) {
            $this->_session->setSchedulerIds($schedulerChunks);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_EbayMultiAccount::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Upload File'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No Jobs To Upload.'));
        }
        $result = $this->resultRedirectFactory->create();
        $result->setPath('ebaymultiaccount/jobScheduler/index');
        return $result;
    }
}
