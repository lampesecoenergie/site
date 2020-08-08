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

namespace Ced\Amazon\Cron\Queue\Report;

use Ced\Amazon\Api\Processor\ReportProcessorInterface;

class Processor extends \Ced\Amazon\Cron\Queue\Processor\Base implements ReportProcessorInterface
{
    /** @var string  */
    public $cacheIdentifier = "report_processor_cron_status";

    /** @var string  */
    public $cacheIdentifierOperationType = "report_operation_type";

    /** @var array  */
    public $operations = [
        \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST => true,
        \Amazon\Sdk\Base::OPERATION_TYPE_GET => true,
    ];

    /** @var string  */
    public $operation = \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST;

    /**
     * Initial Cron Status : Start with listing report
     */
    public $init = [
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA => true,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_INVENTORY => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_LISTING_ACTIVE_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_LISTING_INACTIVE_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_COMPACT => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_LITE => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_LITER => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_CANCELED_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_SOLD_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_DEFECT_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_EURO_AFN_LISTING_STATUS => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_EURO_MFN_LISTING_STATUS => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_GLOBAL_OPPORTUNITIES => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_REFERRAL_FEE_PREVIEW => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_UNSHIPPED_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_FLAT_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_XML_ORDER_DATA => false,
    ];

    public $status = [
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA => true,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_INVENTORY => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_LISTING_ACTIVE_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_LISTING_INACTIVE_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_COMPACT => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_LITE => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_LITER => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_CANCELED_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_SOLD_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_DEFECT_LISTING_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_EURO_AFN_LISTING_STATUS => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_EURO_MFN_LISTING_STATUS => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_GLOBAL_OPPORTUNITIES => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_REFERRAL_FEE_PREVIEW => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_UNSHIPPED_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_FLAT_ORDER_DATA => false,
        \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_XML_ORDER_DATA => false,
    ];

    /** @var string */
    public $type = \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA;

    /** @var \Ced\Amazon\Api\ReportRepositoryInterface */
    public $report;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  */
    public $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    public $productResource;

    /** @var \Magento\Store\Model\StoreManagerInterface  */
    public $storeManager;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\ReportRepositoryInterface $report,
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        parent::__construct($dateTime, $serializer, $search, $filter, $queue, $cache, $config, $logger);
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;

        $this->report = $report;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Run the task
     * @return boolean
     */
    public function execute()
    {
        try {
            // Getting cron status from cache for setting type of feed.
            $status = $this->cache->getValue($this->cacheIdentifier);
            if (isset($status) && !empty($status)) {
                $this->status = $status;
            }

            // Get the status of the next staged action.
            $override = $this->getTypeOverride();
            if (isset($override)) {
                $this->type = $override;
                $this->current = $override;
            } else {
                $this->type = array_search(true, $this->status);
                $this->current = $this->type;
            }
            // Setting the default action to All Listing Request if none are staged for operation.
            if ($this->type == false) {
                $this->type = \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA;
            }

            // Setting the default operation type to 'REQUEST' or 'GET'.
            // Use null for random processing.
            $operationType = $this->cache->getValue($this->cacheIdentifierOperationType);
            if (isset($operationType) && !empty($operationType)) {
                $this->operations = $operationType;
            }

            // Get the operation of the next staged action.
            $this->operation = array_search(true, $this->operations);
            // Setting the default action to Request Operation if none are staged.
            if ($this->operation == false) {
                $this->operation = \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST;
            }

            $this->logger->info(
                "Queue processing: execution started.",
                ['type' => $this->type, 'processor' => self::PROCESSOR_TYPE]
            );

            /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
            $item = $this->queue->pop(
                $this->type,
                [
                    \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                    \Ced\Amazon\Model\Source\Queue\Status::PROCESSED
                ],
                $this->operation
            );

            // $item is empty object if not found with current $this->type
            $list = $this->getList($item);

            // If current set operation have queue records, then process them.
            if ($list->getTotalCount() > 0) {
                /** @var \Ced\Amazon\Api\Data\QueueInterface[] $items */
                $items = $list->getItems();
                $this->process($items);
            } else {
                $this->status[$this->type] = false;

                $this->logger->info(
                    "Queue processing: current status set to false.",
                    ['type' => $this->type, 'status' => $this->status, 'processor' => self::PROCESSOR_TYPE]
                );

                foreach ($this->operations as $operation => $opstatus) {
                    foreach ($this->status as $type => $value) {
                        $this->type = $type;

                        /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
                        $item = $this->queue->pop(
                            $this->type,
                            [
                                \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                                \Ced\Amazon\Model\Source\Queue\Status::PROCESSED
                            ],
                            $operation
                        );

                        // Getting the queue records for current feed type
                        $list = $this->getList($item);

                        if ($list->getTotalCount() > 0) {
                            /** @var \Ced\Amazon\Api\Data\QueueInterface[] $items */
                            $items = $list->getItems();
                            $this->process($items);
                            break;
                        } else {
                            $this->stageNext();
                            continue;
                        }
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->logger->error(
                self::LOGGING_TAG . "failed.",
                [
                    'path' => __METHOD__,
                    'message' => $exception->getMessage(),
                    'exception' => $exception->getTraceAsString(),
                    'processor' => self::PROCESSOR_TYPE
                ]
            );
            return false;
        }
    }

    /**
     * Stage Next Action to execute in status.
     * @return bool
     * @throws \Exception
     */
    public function stageNext()
    {
        // Checking type value. Exit on invalid value.
        $operations = array_keys($this->operations);
        if (!in_array($this->operation, $operations)) {
            $this->logger->error(
                self::LOGGING_TAG . "staging next failed. Invalid type set. ",
                ['type' => $this->operation, 'processor' => self::PROCESSOR_TYPE]
            );
            return false;
        }

        // Setting cron status into the cache
        $this->operations[$this->operation] = false;
        foreach ($operations as $key => $type) {
            if ($type == $this->operation) {
                if (!isset($operations[$key + 1])) {
                    // In case of last index, set to first.
                    $next = $operations[0];
                } else {
                    $next = $operations[$key + 1];
                }
                $this->operations[$next] = true;
                break;
            }
        }

        $this->logger->info(
            "Queue processing: staging next completed.",
            [
                'status' => $this->operations,
                'current' => $this->current,
                'type'=> $this->type,
                'processor' => self::PROCESSOR_TYPE
            ]
        );
        $this->cache->setValue($this->cacheIdentifierOperationType, $this->operations);
        return parent::stageNext();
    }

    /**
     * Get List of Queue Items
     * @param \Ced\Amazon\Api\Data\QueueInterface $item
     * @return \Ced\Amazon\Api\Data\QueueSearchResultsInterface
     */
    private function getList($item)
    {
        /** @var \Magento\Framework\Api\Filter $statusFilter */
        $statusFilter = $this->filter->create();
        $statusFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue($item->getStatus());

        /** @var \Magento\Framework\Api\Filter $typeFilter */
        $typeFilter = $this->filter->create();
        $typeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getType());

        /** @var \Magento\Framework\Api\Filter $operationTypeFilter */
        $operationTypeFilter = $this->filter->create();
        $operationTypeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_OPERATION_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getOperationType());

        /** @var \Magento\Framework\Api\Filter $marketplaceFilter */
        $marketplaceFilter = $this->filter->create();
        $marketplaceFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE)
            ->setConditionType('eq')
            ->setValue($item->getMarketplace());

        /** @var \Magento\Framework\Api\Filter $accountIdFilter */
        $accountIdFilter = $this->filter->create();
        $accountIdFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID)
            ->setConditionType('eq')
            ->setValue($item->getAccountId());

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $criteria->addFilter($statusFilter);
        $criteria->addFilter($typeFilter);
        $criteria->addFilter($operationTypeFilter);
        $criteria->addFilter($marketplaceFilter);
        $criteria->addFilter($accountIdFilter);

        // static size setting for request/get report
        $criteria->setPageSize(5);
        $criteria->setCurrentPage(1);

        // Getting the queue records for current feed type.
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
        $list = $this->queue->getList($criteria->create());
        return $list;
    }

    /**
     * Process queue items
     * @param \Ced\Amazon\Api\Data\QueueInterface[] $items
     * @throws \Exception
     */
    public function process(array $items)
    {
        $itemIds = [];
        $specifics = [];
        $envelope = null;
        /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
        foreach ($items as $item) {
            $requested = false;
            $requestId = null;
            $status = null;
            try {
                $specifics = $item->getSpecifics();
                if ($item->getOperationType() == \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST) {
                    if ($requested == false) {
                        $requestId = $this->report->request($specifics);
                        if ($requestId !== false) {
                            $requested = true;
                        }
                    }

                    if ($requested) {
                        $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::PROCESSED);
                        $specifics['request_id'] = $requestId;
                        $item->setSpecifics(json_encode($specifics));
                        $item->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_GET);
                    }
                } elseif ($item->getOperationType() == \Amazon\Sdk\Base::OPERATION_TYPE_GET) {
                    $status = $this->report->get($specifics['request_id']);

                    if (in_array(
                        $status,
                        [
                            \Ced\Amazon\Model\Source\Feed\Status::DONE,
                            \Ced\Amazon\Model\Source\Feed\Status::DONE_NO_DATA
                        ]
                    )) {
                        $this->update($specifics);
                        $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::DONE);
                    }

                    $specifics['status'] = $status;
                    $item->setSpecifics(json_encode($specifics));
                }
                $item->setExecutedAt($this->dateTime->gmtDate());

                // Adding result for CLI
                $this->addResult(
                    $item->getId(),
                    [
                        "operation" => $this->operation,
                        "requested" => $requested,
                        "request_id" => $requestId,
                        'status' => $status
                    ]
                );
            } catch (\Exception $e) {
                $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::ERROR);
                $this->logger->error(
                    $e->getMessage(),
                    [
                        'path' => __METHOD__,
                        'response' => $specifics,
                        'type' => $this->type,
                        'queue_ids' => $itemIds,
                        'processor' => self::PROCESSOR_TYPE
                    ]
                );
            }

            $this->queue->save($item);
        }

        $this->logger->debug(
            self::LOGGING_TAG . "feed processed.",
            [
                'path' => __METHOD__,
                'response' => $specifics,
                'type' => $this->type,
                'queue_ids' => $itemIds,
                'processor' => self::PROCESSOR_TYPE
            ]
        );

        $this->stageNext();
    }

    /**
     * Update Magento Data By Report Result
     * @param array $specifics
     */
    public function update($specifics)
    {
        if ($specifics['type'] == \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA) {
            $collection = $this->productCollectionFactory->create();
            $collection->addFieldToSelect(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
            if (isset($specifics['ids']) && (!empty($specifics['ids']) && $specifics['ids'] != ['*'])) {
                $productIds = $specifics['ids'];
                $collection->addFieldToFilter('entity_id', [ 'in' => $productIds]);
            }

            $skusToUpdate = $collection->getColumnValues('sku');

            /** @var \Ced\Amazon\Api\Data\ReportInterface $report */
            $report = $this->report->getByRequestId($specifics['request_id']);
            $first = true;
            $statusList = [];
            $headers = [];
            $file = fopen($report->getReportFile(), 'r');

            while (($line = fgetcsv($file, 0, "\t")) !== false) {
                if ($first) {
                    $headers = $line;
                    $first = false;
                } else {
                    $product = [];
                    foreach ($headers as $i => $header) {
                        $product[$header] = $line[$i];
                    }

                    if (in_array($product['seller-sku'], $skusToUpdate)) {
                        $statusList[$product['seller-sku']] = [
                            $specifics['marketplace'] => $product['status']
                        ];
                    }
                }
            }
            fclose($file);

            if (!empty($statusList)) {
                $skus = array_keys($statusList);
                $collection->addFieldToFilter('sku', [ 'in' => $skus]);
                if ($collection->getSize() > 0) {
                    foreach ($collection->getItems() as $item) {
                        if (isset($statusList[$item->getData('sku')])) {
                            $status = $item->getData(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
                            $status = json_decode($status, true);
                            if (is_array($status)) {
                                $status = array_merge($status, $statusList[$item->getData('sku')]);
                            } else {
                                $status = $statusList[$item->getData('sku')];
                            }

                            $item->setData(
                                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS,
                                json_encode($status)
                            );
                        }
                    }

                    $this->save($collection);
                }
            }
        }
    }

    /**
     * Save attribute in a collection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $products
     */
    private function save($products)
    {
        $storeId = null;
        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            try {
                if (isset($storeId)) {
                    $product->setStoreId($storeId);
                }

                $this->productResource->saveAttribute(
                    $product,
                    \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS
                );
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
