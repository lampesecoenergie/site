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

namespace Ced\EbayMultiAccount\Cron;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class UploadCreatedFile
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfigManager;

    /** @var \Ced\EbayMultiAccount\Helper\Logger  */
    public $logger;

    /** @var \Ced\EbayMultiAccount\Model\JobScheduler  */
    public $schedulerCollection;

    /** @var \Ced\EbayMultiAccount\Model\JobScheduler  */
    public $schedulerResource;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public function __construct(
        ScopeConfigInterface $storeManager,
        \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\EbayMultiAccount\Model\JobScheduler $schedulerResource,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->scopeConfigManager = $storeManager;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\EbayMultiAccount\Helper\Order
     */
    public function execute()
    {
        if ($this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/ebaymultiaccount_file_upload_flag')) {
            /** @var \Ced\EbayMultiAccount\Model\JobScheduler $schedulerCollection */
            $accounts = $this->multiAccountHelper->getAllAccounts();
            foreach ($accounts as $account) {
                $accountId = $account->getId();
                $invUpdateResponse = $reviseItemResponse = $fixedPriceResponse = $addItemResponse = $schedulerChunks = array();
                $schedulerCollection = $this->schedulerResource;
                $addItemSchedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_status', 'file_created')
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('threshold_limit', array('lt' => 3))
                    ->addFieldToFilter('scheduler_type', $schedulerCollection::ADDITEM);
                if ($addItemSchedulerCollection->getSize() > 5) {
                    $schedulerChunks = array_chunk($addItemSchedulerCollection->getColumnValues('id'), 5);
                }
                if (isset($schedulerChunks[0]) && count($schedulerChunks[0]) >= 5) {
                    $addItemSchedulerCollection = $this->schedulerCollection->create()
                        ->addFieldToFilter('id', array('in' => $schedulerChunks[0]));
                    $addItemResponse = $this->fileUploadHelper->uploadPreparedFile($addItemSchedulerCollection);
                }

                $schedulerChunks = array();
                $fixedPriceItemSchedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_status', 'file_created')
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('threshold_limit', array('lt' => 3))
                    ->addFieldToFilter('scheduler_type', $schedulerCollection::ADDFIXEDPRICEITEM);
                if ($fixedPriceItemSchedulerCollection->getSize() > 5) {
                    $schedulerChunks = array_chunk($fixedPriceItemSchedulerCollection->getColumnValues('id'), 5);
                }
                if (isset($schedulerChunks[0]) && count($schedulerChunks[0]) >= 5) {
                    $fixedPriceItemSchedulerCollection = $this->schedulerCollection->create()
                        ->addFieldToFilter('id', array('in' => $schedulerChunks[0]));
                    $fixedPriceResponse = $this->fileUploadHelper->uploadPreparedFile($fixedPriceItemSchedulerCollection);
                }

                $schedulerChunks = array();
                $reviseItemSchedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_status', 'file_created')
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('threshold_limit', array('lt' => 3))
                    ->addFieldToFilter('scheduler_type', $schedulerCollection::REVISEITEM);
                if ($reviseItemSchedulerCollection->getSize() > 5) {
                    $schedulerChunks = array_chunk($reviseItemSchedulerCollection->getColumnValues('id'), 5);
                }
                if (isset($schedulerChunks[0]) && count($schedulerChunks[0]) >= 5) {
                    $reviseItemSchedulerCollection = $this->schedulerCollection->create()
                        ->addFieldToFilter('id', array('in' => $schedulerChunks[0]));
                    $reviseItemResponse = $this->fileUploadHelper->uploadPreparedFile($reviseItemSchedulerCollection);
                }

                $schedulerChunks = array();
                $reviseInvSchedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_status', 'file_created')
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('threshold_limit', array('lt' => 3))
                    ->addFieldToFilter('scheduler_type', $schedulerCollection::REVISEINVENTORYSTATUS);
                if ($reviseInvSchedulerCollection->getSize() > 5) {
                    $schedulerChunks = array_chunk($reviseInvSchedulerCollection->getColumnValues('id'), 5);
                }
                if (isset($schedulerChunks[0]) && count($schedulerChunks[0]) >= 5) {
                    $reviseInvSchedulerCollection = $this->schedulerCollection->create()
                        ->addFieldToFilter('id', array('in' => $schedulerChunks[0]));
                    $invUpdateResponse = $this->fileUploadHelper->uploadPreparedFile($reviseInvSchedulerCollection);
                }
            }
            $this->logger->addInfo('File Upload Cron', array('path' => __METHOD__, 'AddItemResponse' => var_export($addItemResponse, true),
                'FixedPriceResponse' => var_export($fixedPriceResponse, true), 'ReviseResponse' => var_export($reviseItemResponse, true), 'InvUpdateResponse' => var_export($invUpdateResponse, true)));
            return true;
        }
        $this->logger->addInfo('File Upload Cron', array('path' => __METHOD__, 'Response' => 'File Upload Cron Disabled from Config'));
        return false;
    }
}
