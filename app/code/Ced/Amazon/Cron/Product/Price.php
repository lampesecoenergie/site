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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Cron\Product;

class Price
{
    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory */
    public $filter;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Ced\Amazon\Repository\Queue */
    public $queue;

    /** @var  \Ced\Amazon\Model\Queue\DataFactory */
    public $data;

    /** @var \Ced\Amazon\Repository\Profile */
    public $profile;

    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Ced\Amazon\Repository\Profile $profile,
        \Ced\Amazon\Repository\Queue $queue,
        \Ced\Amazon\Model\Queue\DataFactory $data,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Config $config
    )
    {
        $this->search = $search;
        $this->filter = $filter;

        $this->profile = $profile;
        $this->config = $config;
        $this->queue = $queue;
        $this->data = $data;
        $this->logger = $logger;
    }

    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        $status = false;
        $productIds = [];
        $message = false;
        $sync = false;
        try {
            $sync = $this->config->getPriceSync();
            if ($sync) {
                /** @var \Magento\Framework\Api\Filter $statusFilter */
                $statusFilter = $this->filter->create();
                $statusFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_STATUS)
                    ->setConditionType('eq')
                    ->setValue(\Ced\Amazon\Model\Source\Profile\Status::ENABLED);

                /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
                $criteria = $this->search->create();
                $criteria->addFilter($statusFilter);
                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->profile->getList($criteria->create());
                $type = $this->config->getPriceType();

                if ($profiles->getTotalCount() > 0) {
                    /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                    $accounts = $profiles->getAccounts();

                    /** @var array $stores */
                    $stores = $profiles->getProfileByStoreIdWise();

                    /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                    foreach ($accounts->getItems() as $accountId => $account) {
                        foreach ($stores as $storeId => $profiles) {
                            $envelope = null;
                            /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                            foreach ($profiles as $profileId => $profile) {
                                $marketplaceIds = $profile->getMarketplaceIds();
                                if ($type ==
                                    \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE) {
                                    // 1. Different price for different countries
                                    foreach ($marketplaceIds as $marketplaceId) {
                                        $status = $this->push($profile, $marketplaceId);
                                    }
                                } else {
                                    // 2. Same price for different countries
                                    $status = $this->push($profile, $profile->getMarketplace());
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->logger->notice(
            "Amazon Cron : All price executed.",
            [
                'enabled' => $sync,
                'status' => $status,
                'count' => count($productIds),
                'exception' => $message
            ]
        );

        return $status;
    }

    /**
     * Push item in queue
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param string $marketplace
     * @return bool
     */
    private function push($profile, $marketplaceId)
    {
        $status = false;
        try {
            // queue
            $productIds = $this->profile
                ->getAssociatedProductIds($profile->getId(), $profile->getStoreId());
            $specifics = [
                'ids' => $productIds,
                'account_id' => $profile->getAccountId(),
                'marketplace' => $marketplaceId,
                'profile_id' => $profile->getId(),
                'store_id' => $profile->getStoreId(),
                'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
            ];
            /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
            $queueData = $this->data->create();
            $queueData->setAccountId($profile->getAccountId());
            $queueData->setMarketplace($marketplaceId);
            $queueData->setSpecifics($specifics);
            $queueData->setType(\Amazon\Sdk\Api\Feed::PRODUCT_PRICING);
            $status = $this->queue->push($queueData);
        } catch (\Exception $e) {
            $this->logger->error(
                "Amazon Cron : All price failed.",
                [
                    'status' => $status,
                    'count' => count($productIds),
                    'exception' => $e->getMessage()
                ]
            );
        }
        return $status;
    }
}
