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

namespace Ced\Amazon\Repository;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Ced\Amazon\Api\Data\AccountSearchResultsInterfaceFactory;
use Ced\Amazon\Model\ResourceModel\Account as AccountResource;
use Ced\Amazon\Model\AccountFactory;
use Ced\Amazon\Model\ResourceModel\Account\CollectionFactory;

class Account implements \Ced\Amazon\Api\AccountRepositoryInterface
{
    const CACHE_IDENTIFIER = "account_";

    /** @var \Ced\Amazon\Model\Cache */
    private $cache;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Account
     */
    private $resource;

    /**
     * @var \Ced\Amazon\Model\AccountFactory
     */
    private $accountFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Ced\Amazon\Api\Data\AccountSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface */
    private $pool = null;

    /**
     * Account constructor.
     * @param \Ced\Amazon\Model\Cache $cache
     * @param AccountResource $resource
     * @param AccountFactory $accountFactory
     * @param CollectionFactory $collectionFactory
     * @param AccountSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Model\ResourceModel\Account $resource,
        \Ced\Amazon\Model\AccountFactory $accountFactory,
        \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\AccountSearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->cache = $cache;
        $this->resource = $resource;
        $this->accountFactory = $accountFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Get object pool
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    private function getPool()
    {
        if (!isset($this->pool)) {
            $this->pool = $this->searchResultsFactory->create();
        }

        return $this->pool;
    }

    /**
     * Get a Account by Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\AccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $account = $this->getPool()->getItem($id);
        if (!isset($account)) {
            $account = $this->accountFactory->create();
            $data = $this->cache->getValue(self::CACHE_IDENTIFIER . $id);
            if (!empty($data)) {
                $account->addData($data);
            } else {
                $this->refresh($id, $account);
            }
        }

        if (!$account->getId()) {
            throw new NoSuchEntityException(__('Account does not exist.'));
        }

        return $account;
    }

    /**
     * TODO: Check if optimized
     * @param array $ids
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getByIds(array $ids = [])
    {
        /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $accounts = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                if (!empty($id)) {
                    $accounts[$id] = $this->getById($id);
                }
            }
        }

        $searchResults->setItems($accounts);
        $searchResults->setTotalCount(count($accounts));
        return $searchResults;
    }

    /**
     * Get all active accounts, Active means enabled and status is valid.
     * @param array $ids, can be filtered by only the given ids
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    public function getAvailableList($ids = [])
    {
        /** @var \Ced\Amazon\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, ['eq' => 1])
            ->addFieldToFilter(
                \Ced\Amazon\Model\Account::COLUMN_STATUS,
                ['eq' => \Ced\Amazon\Model\Source\Account\Status::VALID]
            );

        if (!empty($ids) && is_array($ids)) {
            $collection->addFieldToFilter(\Ced\Amazon\Model\Account::COLUMN_ID, ['in' => $ids])
                ->addFieldToFilter(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, ['eq' => 1]);
        }

        $accounts = [];
        /** @var \Ced\Amazon\Model\Account $account */
        foreach ($collection->getColumnValues(\Ced\Amazon\Model\Account::COLUMN_ID) as $accountId) {
            try {
                $accounts[$accountId] = $this->getById($accountId);
            } catch (\Exception $e) {
                // Silence
            }
        }

        /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($accounts);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Get all Accounts
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Ced\Amazon\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->collectionFactory->create();
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            if (isset($field)) {
                $collection->addOrder(
                    $field,
                    $this->getDirection($sortOrder->getDirection())
                );
            }
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $accounts = [];
        /** @var \Ced\Amazon\Model\Account $account */
        foreach ($collection as $account) {
            $accounts[$account->getId()] = $account;
        }

        /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($accounts);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\AccountInterface $account)
    {
        if ($account->getId() > 0) {
            $this->clean($account->getId());
        }

        $this->resource->save($account);
        return $account->getId();
    }

    /**
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @param int $id
     * @return \Ced\Amazon\Api\Data\AccountInterface $account
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function load(\Ced\Amazon\Api\Data\AccountInterface $account, $id)
    {
        $this->resource->load($account, $id);
        return $account;
    }

    /**
     * Delete a account
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $account = $this->accountFactory->create();
        $account->setId($id);
        if ($this->resource->delete($account)) {
            if (!empty($this->getPool()->getItem($id))) {
                $this->getPool()->setItem($id, null);
            }
            $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clear cache for a Account id
     * @param $id
     */
    public function clean($id)
    {
        if (!empty($this->getPool()->getItem($id))) {
            $this->getPool()->setItem($id, null);
        }

        $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
    }

    /**
     * Refresh account in cache
     * @param $id
     * @param \Ced\Amazon\Api\Data\AccountInterface
     * @throws \Exception
     */
    public function refresh($id, $account = null)
    {
        if (!isset($account)) {
            $account = $this->accountFactory->create();
        }

        $this->resource->load($account, $id);
        $this->cache->setValue(self::CACHE_IDENTIFIER . $id, $account->getData());
        $this->getPool()->setItem($id, $account);
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param \Ced\Amazon\Model\ResourceModel\Account\Collection $collection
     */
    private function addFilterGroupToCollection($group, $collection)
    {
        $fields = [];
        $conditions = [];

        foreach ($group->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $field = $filter->getField();
            $value = $filter->getValue();
            $fields[] = $field;
            $conditions[] = [$condition => $value];
        }

        $collection->addFieldToFilter($fields, $conditions);
    }

    private function getDirection($direction)
    {
        return $direction == SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }
}
