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

class SyncUploadedJob
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfigManager;

    /** @var \Ced\EbayMultiAccount\Helper\Logger  */
    public $logger;

    /** @var \Ced\EbayMultiAccount\Model\FeedDetails  */
    public $feedCollection;

    /** @var \Ced\EbayMultiAccount\Model\JobScheduler  */
    public $schedulerResource;

    public function __construct(
        ScopeConfigInterface $storeManager,
        \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory $feedCollection,
        \Ced\EbayMultiAccount\Model\JobScheduler $schedulerResource,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger
    )
    {
        $this->scopeConfigManager = $storeManager;
        $this->feedCollection = $feedCollection;
        $this->schedulerResource = $schedulerResource;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Ced\EbayMultiAccount\Helper\Order
     */
    public function execute()
    {
        if ($this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/ebaymultiaccount_sync_job_flag')) {
            $syncResponse = array();
            $feedData = $this->feedCollection->create()
                ->addFieldToFilter('threshold_limit', array('lt' => 50))
                ->addFieldToFilter('job_status', array('in' => array('Created', 'Scheduled', 'InProcess')));
            if ($feedData->getSize() > 0) {
                $syncResponse = $this->fileUploadHelper ->syncJobs($feedData);
            }

            $this->logger->addInfo('Job Syncing Cron', array('path' => __METHOD__, 'Response' => var_export($syncResponse, true)));
            return true;
        }
        $this->logger->addInfo('Job Syncing Cron', array('path' => __METHOD__, 'Response' => 'Job Syncing Cron Disabled from Config'));
        return false;
    }
}
