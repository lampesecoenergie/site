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

class ProcessReportFile
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
        if ($this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/ebaymultiaccount_process_report_file_flag')) {
            $ebaymultiaccountFeedIds = $processReportResult = array();
            $feedData = $this->feedCollection->create()
                ->addFieldToFilter('threshold_limit', array('lt' => 100))
                ->addFieldToFilter('job_status', array('in' => array('Processing', 'Completed')))
                ->getFirstItem();
            if ($feedData->getId() > 0) {
                if($feedData->getReportFeedFilePath() == null) {
                    $this->fileUploadHelper->downloadSingleReportFile($feedData);
                }
                $prodIdsToProcess = (!empty($feedData->getUnprocessedProductIds())) ? $feedData->getUnprocessedProductIds() : $feedData->getProductIds();
                $productIds = array_unique(explode(',', $prodIdsToProcess));
                $productChunks = array_chunk($productIds, 100);
                if (isset($productChunks[0])) {
                    $ebaymultiaccountFeedIds = array($feedData->getId() => implode(',', $productChunks[0]));
                }
                $processReportResult = $this->fileUploadHelper->processReportFile($ebaymultiaccountFeedIds);
            }

            $this->logger->addInfo('Job Syncing Cron', array('path' => __METHOD__, 'Response' => var_export($processReportResult, true)));
            return true;
        }
        $this->logger->addInfo('Job Syncing Cron', array('path' => __METHOD__, 'Response' => 'File Creation Cron Disabled from Config'));
        return false;
    }
}
