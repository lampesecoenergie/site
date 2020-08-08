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

use Ced\Amazon\Model\ResourceModel\Report\Collection;
use Magento\Framework\Api\SortOrder;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class Report implements \Ced\Amazon\Api\ReportRepositoryInterface
{
    const MEDIA_PATH = "/amazon/report/";

    /** @var \Magento\Framework\Filesystem\DirectoryList */
    public $directory;

    /** @var \Magento\Framework\Serialize\SerializerInterface */
    public $serializer;

    /** @var \Magento\Backend\Model\UrlInterface */
    public $url;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface */
    public $account;

    /** @var \Ced\Amazon\Model\ResourceModel\Report */
    public $resource;

    /** @var \Ced\Amazon\Model\ReportFactory */
    public $modelFactory;

    /** @var \Ced\Amazon\Model\ResourceModel\Report\CollectionFactory */
    public $collectionFactory;

    /** @var \Ced\Amazon\Api\Data\ReportSearchResultsInterfaceFactory */
    public $searchResultsFactory;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Magento\Framework\Filesystem\Io\File */
    public $file;

    /** @var \Amazon\Sdk\Api\ReportFactory */
    public $report;

    /** @var \Amazon\Sdk\Api\Report\RequestListFactory */
    public $requestList;

    /** @var \Amazon\Sdk\Api\Report\RequestFactory */
    public $request;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Backend\Model\UrlInterface $url,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Model\ResourceModel\Report $resource,
        \Ced\Amazon\Model\ReportFactory $modelFactory,
        \Ced\Amazon\Model\ResourceModel\Report\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\ReportSearchResultsInterfaceFactory $searchResultsFactory,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\Api\Report\RequestFactory $request,
        \Amazon\Sdk\Api\Report\RequestListFactory $requestList,
        \Amazon\Sdk\Api\ReportFactory $report
    ) {
        $this->directory = $directory;
        $this->serializer = $serializer;
        $this->url = $url;

        $this->account = $account;
        $this->resource = $resource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;

        $this->file = $file;
        $this->logger = $logger;

        $this->report = $report;
        $this->request = $request;
        $this->requestList = $requestList;
    }

    /**
     * Get Report by ReportId
     * @param $id
     * @return \Ced\Amazon\Model\Report
     */
    public function getByReportId($id)
    {
        $report = $this->modelFactory->create();
        $this->resource->load($report, $id, \Ced\Amazon\Model\Report::COLUMN_REPORT_ID);
        return $report;
    }

    /**
     * Get Request by ReportId
     * @param $id
     * @return \Ced\Amazon\Model\Report
     */
    public function getByRequestId($id)
    {
        $report = $this->modelFactory->create();
        $this->resource->load($report, $id, \Ced\Amazon\Model\Report::COLUMN_REQUEST_ID);
        return $report;
    }

    /**
     * Request Report from Amazon
     * @param array $specifics ,
     * ['type' => '_GET_MERCHANT_LISTINGS_ALL_DATA_', 'account_id' => 1, 'marketplace' => 'A1AM78C64UM0Y8']
     * @return array|bool
     * @throws \Exception
     */
    public function request($specifics = [])
    {
        $response = false;
        if (isset($specifics['type'])) {
            $type = $specifics['type'];
            $path = $this->createFile('report', $specifics);
            // Saving report file and getting xml content
            if (isset($path)) {
                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                $account = $this->account->getById($specifics['account_id']);

                /**
                 * Sending Report to Amazon
                 * @var \Amazon\Sdk\Api\Report\Request $request
                 */
                $request = $this->request->create(
                    [
                        'config' => $account->getConfig(),
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode(),
                    ]
                );

                if (isset($specifics['marketplace']) && !empty($specifics['marketplace'])) {
                    $request->setMarketplaces($specifics['marketplace']);
                }
                $request->setReportType($type);

                if (isset($specifics['start_date'])) {
                    $request->setStartDate($specifics['start_date']);
                }

                if (isset($specifics['end_date'])) {
                    $request->setEndDate($specifics['end_date']);
                }

                $sent = $request->requestReport();
                if ($sent) {
                    $requestId = $request->getReportRequestId();
                    $reportId = null;
                    $status = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED;

                    //TODO: add get in queue.

                    // Saving in Amazon Reports in DB
                    /** @var \Ced\Amazon\Model\Report $report */
                    $report = $this->modelFactory->create();
                    $report->addData([
                        \Ced\Amazon\Model\Report::COLUMN_ACCOUNT_ID => $specifics['account_id'],
                        \Ced\Amazon\Model\Report::COLUMN_REQUEST_ID => $requestId,
                        \Ced\Amazon\Model\Report::COLUMN_REPORT_ID => $reportId,
                        \Ced\Amazon\Model\Report::COLUMN_TYPE => $type,
                        \Ced\Amazon\Model\Report::COLUMN_STATUS => $status,
                        \Ced\Amazon\Model\Report::COLUMN_REPORT_FILE => $path,
                        \Ced\Amazon\Model\Report::COLUMN_SPECIFICS => json_encode($specifics),
                    ]);

                    $report->save();
                    $response = $requestId;

                    return $response;
                } else {
                    $this->logger->error('Report request failed.', ['path' => __METHOD__, 'specifics' => $specifics]);
                }
            }
        }

        return $response;
    }

    private function createFile($type = 'report', $specifics = [], $code = 'var')
    {
        $path = $this->directory->getPath($code) . DS . 'amazon' . DS . strtolower($type);
        // Check if directory exists
        if (!$this->file->fileExists($path)) {
            $this->file->mkdir($path, 0777, true);
        }

        // File path
        $mpId = isset($specifics['marketplace']) ? $specifics['marketplace'] : '';
        $type = isset($specifics['type']) ? strtolower($specifics['type']) : '';
        $accountId = isset($specifics['account_id']) ? strtolower($specifics['account_id']) : '';
        $filePath = $path . DS . $type . '-' . $accountId . '-' . $mpId . '-' . uniqid() . '.tsv';

        // Check if file exists
        if (!$this->file->fileExists($filePath)) {
            $this->file->write($filePath, '', 0777);
        }

        return $filePath;
    }

    public function sync($id, $report = null)
    {
        if (!isset($report)) {
            $report = $this->getById($id);
        }
        return $this->get($report->getRequestId(), $report);
    }

    /**
     * Get Report by Id
     * @param $id
     * @return \Ced\Amazon\Model\Report
     */
    public function getById($id)
    {
        $report = $this->modelFactory->create();
        $this->resource->load($report, $id);
        return $report;
    }

    /**
     * Sync report result
     * @param $requestId
     * @param \Ced\Amazon\Api\Data\ReportInterface|null $report
     * @return bool
     */
    public function get($requestId, $report = null)
    {
        $status = false;
        if (isset($requestId) && !empty($requestId)) {
            try {
                if (!isset($report)) {
                    /** @var \Ced\Amazon\Api\Data\ReportInterface $report */
                    $report = $this->getByRequestId($requestId);
                }

                $status = $report->getStatus();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                $account = $this->account->getById($report->getAccountId());

                /** @var \Amazon\Sdk\Api\Report\RequestList $requestList */
                $requestList = $this->requestList->create([
                    'config' => $account->getConfig(),
                    'logger' => $this->logger,
                    'mockMode' => $account->getMockMode()
                ]);
                $requestList->setReportTypes($report->getType());
                $requestList->setRequestIds($report->getRequestId());
                $requestList->fetchRequestList();
                $requests = $requestList->getList();
                if (!empty($requests)) {
                    foreach ($requests as $i => $data) {
                        // GeneratedReportId will be null for _DONE_NO_DATA_ status
                        $status = $requestList->getStatus($i);
                        if (in_array(
                            $status,
                            [
                                \Ced\Amazon\Model\Source\Feed\Status::DONE,
                                \Ced\Amazon\Model\Source\Feed\Status::DONE_NO_DATA,
                                \Ced\Amazon\Model\Source\Feed\Status::CANCELLED
                            ]
                        )) {
                            $reportId = $requestList->getReportId($i);
                            if (!empty($reportId)) {
                                /** @var \Amazon\Sdk\Api\Report $reportApi */
                                $reportApi = $this->report->create([
                                    'id' => $reportId,
                                    'config' => $account->getConfig(),
                                    'logger' => $this->logger,
                                    'mockMode' => $account->getMockMode()
                                ]);
                                $reportApi->fetchReport();
                                $reportApi->saveReport($report->getReportFile());
                                $fileInfo = $this->file->getPathInfo($report->getReportFile());
                                $fileName = $fileInfo['basename'];
                                $file = $this->directory->getPath('media') . self::MEDIA_PATH . $fileName;
                                if ($this->file->fileExists($file)) {
                                    $this->file->cp($report->getReportFile(), $file);
                                }
                            }

                            $executedAt = date('Y-m-d H:i:s', strtotime($requestList->getCompletedDate($i)));
                            $report->setStatus(\Ced\Amazon\Model\Source\Report\Status::DONE);
                            $report->setReportId($reportId);
                            $report->setExecutedAt($executedAt);
                            $this->save($report);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }

        return $status;
    }

    /**
     * Save
     * @param \Ced\Amazon\Api\Data\ReportInterface $report
     * @return int
     * @throws \Exception
     */
    public function save(\Ced\Amazon\Api\Data\ReportInterface $report)
    {
        $this->resource->save($report);
        return $report->getId();
    }

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

        /** @var \Ced\Amazon\Model\Report $item */
        foreach ($collection as &$item) {
            $items[$item->getId()] = $item;
        }

        /** @var \Ced\Amazon\Api\Data\ReportSearchResultsInterface $searchResults */
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
            $conditions[] = [$condition => $value];
        }

        $collection->addFieldToFilter($fields, $conditions);
    }

    private function getDirection($direction)
    {
        return $direction == SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }

    private function updateMediaFile($path)
    {
        $url = '';
        if (isset($path) && !empty($path)) {
            $fileInfo = $this->file->getPathInfo($path);
            $fileName = $fileInfo['basename'];
            if ($this->file->fileExists($path)) {
                $cpDir = $this->directory->getPath('media') . "/amazon/";
                if (!$this->file->fileExists($cpDir)) {
                    $this->file->mkdir($cpDir);
                }

                $this->file->cp($path, $cpDir . $fileName);
                if ($this->file->fileExists($cpDir . $fileName)) {
                    $url = $this->url
                            ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                        "amazon/" . $fileName;
                }
            }
        }

        return $url;
    }

    /**
     * Clear old records
     * @param null $created
     * @param null $collection
     * @return boolean
     * @throws \Exception
     */
    public function clearRecords($created = null, $collection = null)
    {
        if (!isset($collection) || !$collection instanceof Collection) {
            /** @var \Ced\Amazon\Model\ResourceModel\Report\Collection $collection */
            $collection = $this->collectionFactory->create();
        }

        if (isset($created)) {
            $collection->addFieldToFilter(\Ced\Amazon\Model\Report::COLUMN_EXECUTED_AT, ['lteq' => $created]);
        }

        $size = $status = $collection->getSize();
        if (isset($collection) && $size > 0) {
            /** @var \Ced\Amazon\Model\Report $report */
            foreach ($collection as $report) {
                $reportFile = $report->getReportFile();
                if ($this->file->fileExists($reportFile)) {
                    $this->file->rm($reportFile);
                }

                try {
                    $this->resource->delete($report);
                    $status = $report->isDeleted();
                } catch (\Exception $e) {
                    // Silence
                }
            }
        }

        return $status;
    }
}
