<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Cron;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    public $helper;

    /**
     * Config Manager
     * @var \Ced\EbayMultiAccount\Helper\EbayMultiAccount
     */
    public $ebaymultiaccountHelper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;

    public $productchange;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\EbayMultiAccount\Helper\Data $helperData,
        \Ced\EbayMultiAccount\Helper\EbayMultiAccount $ebaymultiaccountHelper,
        \Ced\EbayMultiAccount\Model\Productchange $productchange,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data');
        $this->logger = $logger;
        $this->ebaymultiaccountHelper = $ebaymultiaccountHelper;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
    }


    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoSync = $scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/inventory_cron');
        if ($autoSync) {
            $collection = $this->productchange->getCollection();
            $type = \Ced\EbayMultiAccount\Model\Productchange::CRON_TYPE_INVENTORY;
            $collection->addFieldToFilter('cron_type', $type);
            $ids = [];
            foreach ($collection as $pchange) {
                $ids[] = $pchange->getProductId();
            }
            $successIds = array();
            $accountCollection = $this->multiAccountHelper->getAllAccounts('true');
            foreach ($accountCollection as $account) {
                $accountId = $account->getId();
                if ($this->_coreRegistry->registry('ebay_account'))
                    $this->_coreRegistry->unregister('ebay_account');
                $account = $this->multiAccountHelper->getAccountRegistry($accountId);
                $this->helper->updateAccountVariable();
                $this->ebaymultiaccountHelper->updateAccountVariable();
                $successIds = $this->updateInventoryOnEbayMultiAccount($ids);
                if (isset($successIds) && is_array($successIds) && count($successIds) > 0) {
                    $this->logger->addInfo('Update Inventory Cron', array('path' => __METHOD__, 'Response' => 'Success - ' . var_export($successIds, true), 'Account Id' => $accountId ));
                    continue;
                }
                $this->logger->addInfo('Update Inventory Cron', array('path' => __METHOD__, 'Response' => 'Failure' . var_export($successIds, true), 'Account Id' => $accountId ));
            }

            if (isset($successIds) && is_array($successIds) && count($successIds) > 0) {
                $this->productchange->deleteFromProductChange($successIds, $type);
                $this->logger->addInfo('Update Inventory Cron', array('path' => __METHOD__, 'Response' => 'Success - ' . var_export($successIds, true)));
                return true;
            }
            $this->logger->addInfo('Update Inventory Cron', array('path' => __METHOD__, 'Response' => 'Failure' . var_export($successIds, true)));
            return false;

        } else {
            $this->logger->addInfo('EbayMultiAccount Inventory Cron', array('path' => __METHOD__, 'Response' => 'EbayMultiAccount Inventory Cron Disabled from Config'));
            return false;
        }

    }

    public function updateInventoryOnEbayMultiAccount($ids) {
        $successIds = array();
        $error = $finalXml = '';
        $checkError = false;
        foreach ($ids as $id) {
            $finaldata = $this->ebaymultiaccountHelper->getInventoryPrice($id);
            if ($finaldata['type'] == 'success') {
                $successIds[] = $id;
                $checkError = true;
                $finalXml .= $finaldata['data'];
            } else {
                $error .= $finaldata['data'];
            }
        }
        if ($error) {
            $message['error'] = $error;
        }
        if ($checkError) {
            $variable = "ReviseInventoryStatus";
            $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
            $xmlFooter = '</ReviseInventoryStatusRequest>';
            $return = $xmlHeader . $finalXml . $xmlFooter;
            $cpPath = $this->helperData->createFeed($return, $variable);
            $invPriceSyncOnEbayMultiAccount = $this->helperData->sendHttpRequest($return, $variable, 'server');
            $this->helperData->responseParse($invPriceSyncOnEbayMultiAccount, $variable, $cpPath);

            if ($invPriceSyncOnEbayMultiAccount->Ack == "Success" || $invPriceSyncOnEbayMultiAccount->Ack == "Warning" || $invPriceSyncOnEbayMultiAccount->Ack == "PartialFailure") {
                return $successIds;
            }
        }
        return false;
    }
}
