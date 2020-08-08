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

class Queue implements \Ced\Amazon\Api\QueueRepositoryInterface
{
    /** @var \Ced\Amazon\Helper\Config  */
    public $config;

    /** @var \Ced\Amazon\Model\ResourceModel\Queue  */
    public $resource;

    /** @var \Ced\Amazon\Model\QueueFactory  */
    public $modelFactory;

    /** @var \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory  */
    public $collectionFactory;

    /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterfaceFactory  */
    public $searchResultsFactory;

    public $ids = [];

    public function __construct(
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Model\ResourceModel\Queue $resource,
        \Ced\Amazon\Model\QueueFactory $modelFactory,
        \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\QueueSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->config = $config;
        $this->resource = $resource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Add item to queue
     * @param \Ced\Integrator\Api\Data\Queue\DataInterface $data
     * @return bool
     * @throws  \Exception
     */
    public function push(\Ced\Integrator\Api\Data\Queue\DataInterface $data)
    {
        $ids = $data->getIds();
        if (isset($ids) && !empty($ids)) {
            $this->clearIds();
            $chunks = array_chunk($ids, $this->config->getQueueSize($data->getType()));
            foreach ($chunks as $chunk) {
                /** @var \Ced\Amazon\Model\Queue $queue */
                $queue = $this->modelFactory->create();
                $specifics = $data->getSpecifics();
                $specifics['ids'] = $chunk;
                $queue->addData([
                    'type' => $data->getType(),
                    'account_id' => $data->getAccountId(),
                    'marketplace' => $data->getMarketplace(),
                    'depends' => (int)$data->getDepends(),
                    'status' => \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                    'operation_type' => $data->getOperationType(),
                    'priorty' => $data->getPriorty(),
                    'specifics' => json_encode($specifics),
                    'executed_at' => null,
                ]);

                $this->resource->save($queue);
                $this->addId($queue->getId());
            }

            return true;
        }

        return false;
    }

    /**
     * Get first item with provided type and status
     * @param string $type
     * @param string|array $status
     * @param string|array|null $operation
     * @return \Ced\Amazon\Api\Data\QueueInterface
     */
    public function pop($type, $status = '_SUBMITTED_', $operation = 'Update')
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Ced\Amazon\Model\Queue::COLUMN_TYPE,
                $type
            );

        if (is_array($status)) {
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Queue::COLUMN_STATUS,
                ['in' => $status]
            );
        } else {
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Queue::COLUMN_STATUS,
                $status
            );
        }

        if (isset($operation) && is_array($operation)) {
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Queue::COLUMN_OPERATION_TYPE,
                ['in' => $operation]
            );
        } elseif (isset($operation)) {
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Queue::COLUMN_OPERATION_TYPE,
                $operation
            );
        }

        /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
        $item = $collection->setPageSize(1)->setCurPage(1)->getFirstItem();
        return $item;
    }

    public function clearIds()
    {
        $this->ids = [];
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function addId($id)
    {
        $this->ids[$id] = $id;
    }

    /**
     * Save
     * @param \Ced\Amazon\Api\Data\QueueInterface $queue
     * @return int
     * @throws \Exception
     */
    public function save(\Ced\Integrator\Api\Data\QueueInterface $queue)
    {
        $this->resource->save($queue);
        return $queue->getId();
    }

    /**
     * Add feed id to PROCESSED queues
     * @param array $ids
     * @param $feedId
     */
    public function addFeedId(array $ids = [], $feedId = "0")
    {
        if (!empty($ids)) {
            $items = $this->collectionFactory->create()->addFieldToFilter('id', ['in' => $ids])
                ->addFieldToSelect(['id', 'specifics']);
            if ($items->getSize() > 0) {
                /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
                foreach ($items->getItems() as $item) {
                    $specifics = $item->getSpecifics();
                    $specifics['feed_id'] = $feedId;
                    $item->setSpecifics(json_encode($specifics));
                }

                $items->save();
            }
        }
    }

    /**
     * Get a Data by Id
     * @param string $id
     * @return \Ced\Integrator\Api\Data\DataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    /**
     * Delete a data
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Clear cache data for id
     * @param $id
     */
    public function clean($id)
    {
        // TODO: Implement clean() method.
    }

    /**
     * Refresh data in cache
     * @param $id
     * @throws \Exception
     */
    public function refresh($id)
    {
        // TODO: Implement refresh() method.
    }

    /**
     * Get all Data
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\QueueSearchResultsInterface
     * @throws \Exception
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
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
        $collection->addFieldToSelect('*');
        $collection->load();
        $items = [];

        /** @var \Ced\Amazon\Model\Queue $item */
        foreach ($collection as &$item) {
            //$item['specifics'] = json_decode($item['specifics'], true);
            $items[$item->getId()] = $item;
        }

        //TODO: setTotalCount is without pagesize
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param \Ced\Amazon\Model\ResourceModel\Queue\Collection $collection
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
            $conditions[] = [$condition=>$value];
        }

        $collection->addFieldToFilter($fields, $conditions);
    }

    private function getDirection($direction)
    {
        return $direction == SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }
}
