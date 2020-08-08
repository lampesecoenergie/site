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

class CreateScheduledFile
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

    public function __construct(
        ScopeConfigInterface $storeManager,
        \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\EbayMultiAccount\Model\JobScheduler $schedulerResource,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger
    )
    {
        $this->scopeConfigManager = $storeManager;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Ced\EbayMultiAccount\Helper\Order
     */
    public function execute()
    {
        if ($this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/ebaymultiaccount_file_creation_flag')) {
            $createdFile = array();
            /** @var \Ced\EbayMultiAccount\Model\JobScheduler $schedulerCollection */
            $schedulerCollection = $this->schedulerCollection->create()
                ->addFieldToFilter('cron_status', 'scheduled')
                ->addFieldToFilter('threshold_limit', array('lt' => 1))
                ->getFirstItem();
            if ($schedulerCollection->getId() > 0) {
                $schedulerId = $schedulerCollection->getId();
                /** @var \Ced\EbayMultiAccount\Model\JobScheduler $schedulerData */
                $schedulerData = $this->schedulerResource->load($schedulerId);
                try {
                    if (sizeof($schedulerData) > 0) {
                        $productIds = $schedulerData->getProductIds();
                        $productIds = is_string($productIds) ? explode(",", $productIds) : array();
                        if ($schedulerData->getSchedulerType() == $schedulerData::REVISEINVENTORYSTATUS) {
                            $createdFile = $this->fileUploadHelper->prepareInventoryUpdateFile($productIds, $schedulerData->getSchedulerType(), $schedulerData->getAccountId());
                        } else {
                            $createdFile = $this->fileUploadHelper->prepareUploadFile($productIds, $schedulerData->getSchedulerType(), $schedulerData->getAccountId());
                        }
                        if (isset($createdFile['feed_file'])) {
                            $schedulerData->setFeedFilePath($createdFile['feed_file'])
                                ->setCronStatus('file_created');
                        }
                        $schedulerData->setThresholdLimit((int) $schedulerData->getThresholdLimit() + 1);
                        $schedulerData->save();
                    }
                } catch (\Exception $e) {
                    $this->logger->addInfo('File Creation Cron', array('path' => __METHOD__, 'exception' => $e->getMessage(), 'Response' => var_export($createdFile, true)));
                    return false;
                }
            }

            $this->logger->addInfo('File Creation Cron', array('path' => __METHOD__, 'Response' => var_export($createdFile, true)));
            return true;
        }
        $this->logger->addInfo('File Creation Cron', array('path' => __METHOD__, 'Response' => 'File Creation Cron Disabled from Config'));
        return false;
    }
}
