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

use Ced\Amazon\Api\Data\OrderSearchResultsInterfaceFactory;
use Ced\Amazon\Model\OrderFactory;
use Ced\Amazon\Model\ResourceModel\Order as OrderResource;
use Ced\Amazon\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

class Order implements \Ced\Amazon\Api\OrderRepositoryInterface
{
    /**
     * @var \Ced\Amazon\Model\ResourceModel\Order
     */
    private $resource;

    /**
     * @var \Ced\Amazon\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Ced\Amazon\Api\Data\OrderSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /** @var \Ced\Amazon\Api\Data\OrderSearchResultsInterface  */
    private $pool = null;

    /** @var SerializerInterface  */
    private $serializer;

    /**
     * Order constructor.
     * @param SerializerInterface $serializer
     * @param OrderResource $resource
     * @param OrderFactory $orderFactory
     * @param CollectionFactory $collectionFactory
     * @param OrderSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        SerializerInterface $serializer,
        \Ced\Amazon\Model\ResourceModel\Order $resource,
        \Ced\Amazon\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\OrderSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Get object pool
     * @return \Ced\Amazon\Api\Data\OrderSearchResultsInterface
     */
    private function getPool()
    {
        if (!isset($this->pool)) {
            $this->pool = $this->searchResultsFactory->create();
        }

        return $this->pool;
    }

    /**
     * Get a Order by Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $order = $this->getPool()->getItem($id);
        if (!isset($order)) {
            $order = $this->orderFactory->create();
            $this->refresh($id, $order);
        }

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order does not exist.'));
        }

        return $order;
    }

    /**
     * Check By Magento Order Id
     * @param $id
     * @return boolean
     */
    public function isMarketplaceOrder($id)
    {
        return $this->resource->isMarkeplaceOrder($id);
    }

    /**
     * Get By Magento Order Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\OrderInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId($id)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $id, \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID);

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order does not exist.'));
        }

        return $order;
    }

    /**
     * Get By Amazon Purchase Order Id
     * @param string $poId
     * @return \Ced\Amazon\Api\Data\OrderInterface
     * @throws NoSuchEntityException
     */
    public function getByPurchaseOrderId($poId)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $poId, \Ced\Amazon\Model\Order::COLUMN_PO_ID);

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order does not exist.'));
        }

        return $order;
    }

    /**
     * Create an Order in Amazon table
     * @param $accountId
     * @param \Magento\Sales\Model\Order|null $order
     * @param \Amazon\Sdk\Api\Order $data
     * @param array $items
     * @return \Ced\Amazon\Model\Order|null
     * @throws \Exception
     */
    public function create($accountId, $order = null, \Amazon\Sdk\Api\Order $data = null, $items = [])
    {
        try {
            // after save order
            $orderPlace = date("Y-m-d H:i:s", strtotime($data->getPurchaseDate()));
            $status = $data->getOrderStatus();
            $status = ($status ==
                \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED) ?
                \Ced\Amazon\Model\Source\Order\Status::NOT_IMPORTED : $status;

            /** @var \Ced\Amazon\Model\Order $marketplaceOrder */
            $marketplaceOrder = $this->orderFactory->create();
            $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID, $accountId);
            $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_PO_ID, $data->getAmazonOrderId());
            $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID, $data->getMarketplaceId());
            $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_PO_DATE, $orderPlace);
            $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, $status);
            $marketplaceOrder->setData(
                \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA,
                $this->serializer->serialize($data->getData())
            );

            if (isset($order) && !empty($order->getId())) {
                $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID, $order->getId());
                $marketplaceOrder->setData(
                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID,
                    $order->getIncrementId()
                );
                if ($marketplaceOrder->getStatus() == \Ced\Amazon\Model\Source\Order\Status::NOT_IMPORTED) {
                    $marketplaceOrder->setData(
                        \Ced\Amazon\Model\Order::COLUMN_STATUS,
                        \Ced\Amazon\Model\Source\Order\Status::IMPORTED
                    );
                }
            }

            if (!empty($items)) {
                $marketplaceOrder->setData(
                    \Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS,
                    $this->serializer->serialize($items)
                );
            }

            $this->resource->save($marketplaceOrder);
            if (!$marketplaceOrder->getId()) {
                throw new \Exception(__('Order create failed.'));
            }
            return $marketplaceOrder;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * TODO: Check if optimized
     * @param array $ids
     * @return \Ced\Amazon\Api\Data\OrderSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getByIds(array $ids = [])
    {
        /** @var \Ced\Amazon\Api\Data\OrderSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $orders = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                if (!empty($id)) {
                    $orders[$id] = $this->getById($id);
                }
            }
        }

        $searchResults->setItems($orders);
        $searchResults->setTotalCount(count($orders));
        return $searchResults;
    }

    public function getByAmazonOrderIds($orderIdList = [], $accountId = null, $status = [])
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_PO_ID, ['in' => $orderIdList])
            ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID, [['eq' => ""], ['null' => true]]);
        if (!empty($status)) {
            $collection->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_STATUS, ['in' => $status]);
        }

        if (!empty($accountId)) {
            $collection->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);
        }

        return $collection;
    }

    /**
     * Get all Orders
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\OrderSearchResultsInterface
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
        $collection->load();

        $orders = [];
        /** @var \Ced\Amazon\Model\Order $order */
        foreach ($collection as $order) {
            $orders[$order->getId()] = $order;
        }

        /** @var \Ced\Amazon\Api\Data\OrderSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($orders);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Ced\Amazon\Api\Data\OrderInterface $order
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\OrderInterface $order)
    {
        if ($order->getId() > 0) {
            $this->clean($order->getId());
        }

        $this->resource->save($order);
        return $order->getId();
    }

    /**
     * @param \Ced\Amazon\Api\Data\OrderInterface $order
     * @param int $id
     * @return \Ced\Amazon\Api\Data\OrderInterface $order
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function load(\Ced\Amazon\Api\Data\OrderInterface $order, $id)
    {
        $this->resource->load($order, $id);
        return $order;
    }

    /**
     * Delete a order
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $order = $this->orderFactory->create();
        $order->setId($id);
        if ($this->resource->delete($order)) {
            if (!empty($this->getPool()->getItem($id))) {
                $this->getPool()->setItem($id, null);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clear cache for a Order id
     * @param $id
     */
    public function clean($id)
    {
        if (!empty($this->getPool()->getItem($id))) {
            $this->getPool()->setItem($id, null);
        }
    }

    /**
     * Refresh order in cache
     * @param $id
     * @param \Ced\Amazon\Api\Data\OrderInterface
     * @throws \Exception
\     */
    public function refresh($id, $order = null)
    {
        if (!isset($order)) {
            $order = $this->orderFactory->create();
        }

        $this->resource->load($order, $id);
        $this->getPool()->setItem($id, $order);
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param \Ced\Amazon\Model\ResourceModel\Order\Collection $collection
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
