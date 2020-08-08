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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Setup;

class Migration
{
    const CONFIG_PATH_MODE = "amazon_config/amazon_setting/mode";
    const CONFIG_PATH_SELLER_ID = "amazon_config/amazon_setting/seller_id";
    const CONFIG_PATH_MARKETPLACE = "amazon_config/amazon_setting/marketplace_ids";
    const CONFIG_PATH_AWS_ACCESS_ID = "amazon_config/amazon_setting/aws_access_id";
    const CONFIG_PATH_SECRET_KEY = "amazon_config/amazon_setting/secret_id";
    const CONFIG_PATH_STORE_ID = "amazon_config/amazon_setting/storeid";

    /** @var \Ced\Amazon\Model\AccountFactory  */
    public $accountFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    public $scopeConfigManager;

    /** @var \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory  */
    public $orderCollectionFactory;

    /**
     * Migration constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Ced\Amazon\Model\AccountFactory $accountFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Ced\Amazon\Model\AccountFactory $accountFactory
    ) {
        $this->accountFactory = $accountFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfigManager = $scopeConfig;
    }

    public function migrateAccount()
    {
        $accountId = null;
        $mode = $this->scopeConfigManager->getValue(self::CONFIG_PATH_MODE);
        $sellerId = $this->scopeConfigManager->getValue(self::CONFIG_PATH_SELLER_ID);
        $marketplaceIds = $this->scopeConfigManager->getValue(self::CONFIG_PATH_MARKETPLACE);
        $accessKeyId = $this->scopeConfigManager->getValue(self::CONFIG_PATH_AWS_ACCESS_ID);
        $secretKey = $this->scopeConfigManager->getValue(self::CONFIG_PATH_SECRET_KEY);
        $storeId = $this->scopeConfigManager->getValue(self::CONFIG_PATH_STORE_ID);
        $storeId = empty($storeId) ? 0 : $storeId;
        $status = \Ced\Amazon\Model\Source\Account\Status::VALID;

        if (!empty($sellerId) && !empty($marketplaceIds) && !empty($secretKey)) {
           /** @var \Ced\Amazon\Model\Account $account */
            $account = $this->accountFactory->create();
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_NAME, 'Amazon');
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_MODE, $mode);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_SELLER_ID, $sellerId);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_MARKETPLACE, $marketplaceIds);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID, $accessKeyId);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_SECRET_KEY, $secretKey);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_STORE_ID, $storeId);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, 1);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_STATUS, $status);
            $account->setData(\Ced\Amazon\Model\Account::COLUMN_NOTES, 'Account imported via upgrade 0.0.2 to 0.0.3.');
            $account->save();
            $accountId = $account->getId();
        }

        return $accountId;
    }

    public function updateOrders($accountId = null)
    {
        if (isset($accountId) && !empty($accountId)) {
            /** @var \Ced\Amazon\Model\Account $account */
            $account = $this->accountFactory->create()->load($accountId);
            $marketplaceId = $account->getDefaultMarketplace();
            /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $orders */
            $orders = $this->orderCollectionFactory->create();
            /** @var \Ced\Amazon\Model\Order $order */
            foreach ($orders as $order) {
                $order->setData(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID, $marketplaceId);
                $order->setData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID, $accountId);
            }

            $orders->save();
        }
    }

    public function updateProfiles()
    {
        //TODO: dev
    }
}
