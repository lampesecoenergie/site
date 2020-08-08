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

namespace Ced\Amazon\Helper\Product;

class Status
{
    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search  */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory  */
    public $filter;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  */
    public $products;

    /** @var \Ced\Amazon\Api\ProfileRepositoryInterface  */
    public $profile;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory  */
    public $queueDataFactory;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface  */
    public $account;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface  */
    public $feed;

    /** @var \Ced\Amazon\Helper\Config  */
    public $config;

    /** @var \Ced\Amazon\Helper\Logger  */
    public $logger;

    /** @var \Amazon\Sdk\Api\Report\RequestFactory  */
    public $request;

    /** @var \Amazon\Sdk\Api\Report\RequestListFactory  */
    public $requestList;

    /** @var \Amazon\Sdk\Api\ReportFactory  */
    public $report;

    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\ProfileRepositoryInterface $profile,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\Api\Report\RequestFactory $request,
        \Amazon\Sdk\Api\Report\RequestListFactory $requestList,
        \Amazon\Sdk\Api\ReportFactory $report
    ) {
        $this->search = $search;
        $this->filter = $filter;

        $this->products = $productsFactory;

        $this->account = $account;
        $this->profile = $profile;
        $this->feed = $feed;
        $this->queue = $queue;
        $this->queueDataFactory = $queueDataFactory;
        $this->config = $config;
        $this->logger = $logger;

        $this->report = $report;
        $this->request = $request;
        $this->requestList = $requestList;
    }

    /**
     * Sync the values for provided ids
     * @param array $ids
     * @param bool $throttle
     * @return boolean
     * @throws \Exception
     */
    public function sync(array $ids = [], $throttle = true)
    {
        $status = false;
        if (isset($ids) && !empty($ids)) {
            $profileIds = $this->profile->getProfileIdsByProductIds($ids);
            if (!empty($profileIds)) {
                /** @var \Magento\Framework\Api\Filter $idsFilter */
                $idsFilter = $this->filter->create();
                $idsFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_ID)
                    ->setConditionType('in')
                    ->setValue($profileIds);

                /** @var \Magento\Framework\Api\Filter $statusFilter */
                $statusFilter = $this->filter->create();
                $statusFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_STATUS)
                    ->setConditionType('eq')
                    ->setValue(\Ced\Amazon\Model\Source\Profile\Status::ENABLED);

                /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
                $criteria = $this->search->create();
                $criteria->addFilter($statusFilter);
                $criteria->addFilter($idsFilter);
                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->profile->getList($criteria->create());
                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                        foreach ($profiles as $profileId => $profile) {
                            if ($throttle == true) {
                                foreach ($profile->getMarketplaceIds() as $marketplace) {
                                    // queue
                                    $productIds = $this->profile
                                        ->getAssociatedProductIds($profileId, $storeId,  $ids);
                                    $specifics = [
                                        'ids' => $productIds,
                                        'account_id' => $accountId,
                                        'marketplace' => $marketplace,
                                        'profile_id' => $profileId,
                                        'store_id' => $storeId,
                                        'type' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA,
                                    ];
                                    /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
                                    $queueData = $this->queueDataFactory->create();
                                    $queueData->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_REQUEST);
                                    $queueData->setAccountId($accountId);
                                    $queueData->setMarketplace($marketplace);
                                    $queueData->setSpecifics($specifics);
                                    $queueData->setType(
                                        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA
                                    );
                                    $status = $this->queue->push($queueData);
                                }
                            } else {
                                // TODO: add direct action with get api.
                            }
                        }
                    }
                }
            }
        }

        return $status;
    }
}
