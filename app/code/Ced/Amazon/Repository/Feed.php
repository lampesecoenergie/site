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

use Ced\Amazon\Model\ResourceModel\Feed\Collection;
use Magento\Framework\Api\SortOrder;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class Feed implements \Ced\Amazon\Api\FeedRepositoryInterface
{
    const MEDIA_PATH = "/amazon/response/";

    /** @var \Magento\Framework\Filesystem\DirectoryList */
    public $directory;

    /** @var \Magento\Framework\Serialize\SerializerInterface */
    public $serializer;

    /** @var \Magento\Backend\Model\UrlInterface */
    public $url;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface */
    public $account;

    /** @var \Ced\Amazon\Model\ResourceModel\Feed */
    public $resource;

    /** @var \Ced\Amazon\Model\FeedFactory */
    public $modelFactory;

    /** @var \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory */
    public $collectionFactory;

    /** @var \Ced\Amazon\Api\Data\FeedSearchResultsInterfaceFactory */
    public $searchResultsFactory;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Magento\Framework\Filesystem\Io\File */
    public $file;

    /** @var \Amazon\Sdk\Api\FeedFactory */
    public $feed;

    /** @var \Amazon\Sdk\Api\Feed\ResultFactory */
    public $result;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Backend\Model\UrlInterface $url,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Model\ResourceModel\Feed $resource,
        \Ced\Amazon\Model\FeedFactory $modelFactory,
        \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\FeedSearchResultsInterfaceFactory $searchResultsFactory,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\Api\FeedFactory $feed,
        \Amazon\Sdk\Api\Feed\ResultFactory $result
    )
    {
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

        $this->feed = $feed;
        $this->result = $result;
    }

    /**
     * Get Api Results By Feed Id
     * @param string $feedId
     * @param string $accountId
     * @return bool|string
     */
    public function getResultByFeedId($feedId, $accountId)
    {
        $response = null;

        try {
            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($accountId);

            /** @var \Amazon\Sdk\Api\Feed\Result $result */
            $result = $this->result->create(
                [
                    'id' => $feedId,
                    'config' => $account->getConfig(),
                    'logger' => $this->logger,
                    'mockMode' => $account->getMockMode()
                ]
            );

            $result->fetchFeedResult();
            $response = $result->getRawFeed();
        } catch (\Exception $e) {
            // Silence
        }

        return $response;
    }

    /**
     * Get Feed by FeedId
     * @param $feedId
     * @return \Ced\Amazon\Model\Feed
     */
    public function getByFeedId($feedId)
    {
        $feed = $this->modelFactory->create();
        $this->resource->load($feed, $feedId, \Ced\Amazon\Model\Feed::COLUMN_FEED_ID);
        return $feed;
    }

    /**
     * TODO: FIX duplicate products are set in envelope. Duplicate queues are set in queues. Merge queues, remove duplicity.
     * Send Feed to Amazon
     * @param \Amazon\Sdk\Envelope|null $envelope
     * @param array $specifics
     * @return array|bool|mixed
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function send(\Amazon\Sdk\Envelope $envelope = null, $specifics = [])
    {
        $response = false;
        if (isset($envelope) && $envelope->index > 0) {
            $type = $specifics['type'];
            $path = $this->createFile('feed', $type);
            // Saving feed file and getting xml content
            $content = $envelope->getData('xml', $path);
            if (isset($content) && !empty($content) && is_string($content)) {
                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                $account = $this->account->getById($specifics['account_id']);

                $config = $account->getConfig();
                if (isset($specifics['marketplace']) && !empty($specifics['marketplace'])) {
                    $marketplaceIds = explode(',', $specifics['marketplace']);
                    $config->setMarketplaceId($marketplaceIds);
                }

                /**
                 * Sending Feed to Amazon
                 * @var \Amazon\Sdk\Api\Feed
                 */
                $amz = $this->feed->create(
                    [
                        'config' => $config,
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode(),
                    ]
                );

                // Feed types listed in documentation
                $amz->setFeedType($type);
                $amz->setFeedContent($content);
                $amz->submitFeed();

                $response = $amz->getResponse();

                if ($response) {
                    $responsePath = $this->createFile('response', $type);
                    $lastResponse = $amz->getLastResponse();
                    // Writing response to file
                    if (isset($lastResponse['body'])) {
                        /** @var boolean $written */
                        $written = $this->file->write($responsePath, $lastResponse['body'], 0777);
                        if ($written == false) {
                            $this->logger->error(
                                "Feed response write to file failed.",
                                [
                                    'specifics' => $specifics,
                                    'response' => $response,
                                    'path' => __METHOD__,
                                ]
                            );
                        }
                    }

                    // Saving in Amazon Feeds in DB
                    /** @var \Ced\Amazon\Model\Feed $feed */
                    $feed = $this->modelFactory->create();
                    $feed->addData([
                        \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID => $specifics['account_id'],
                        \Ced\Amazon\Model\Feed::COLUMN_FEED_ID => $response['FeedSubmissionId'],
                        \Ced\Amazon\Model\Feed::COLUMN_CREATED_DATE => $response['SubmittedDate'],
                        \Ced\Amazon\Model\Feed::COLUMN_EXECUTED_DATE => $response['SubmittedDate'],
                        \Ced\Amazon\Model\Feed::COLUMN_STATUS => $response['FeedProcessingStatus'],
                        \Ced\Amazon\Model\Feed::COLUMN_TYPE => $response['FeedType'],
                        \Ced\Amazon\Model\Feed::COLUMN_FEED_FILE => $path,
                        \Ced\Amazon\Model\Feed::COLUMN_RESPONSE_FILE => $responsePath,
                        \Ced\Amazon\Model\Feed::COLUMN_SPECIFICS => json_encode($specifics),
                        \Ced\Amazon\Model\Feed::COLUMN_PRODUCT_IDS => isset($specifics['ids']) ?
                            $this->serializer->serialize($specifics['ids']) : '[]',
                    ]);

                    $this->resource->save($feed);
                    $response['Id'] = $feed->getId();

                    return $response;
                }
            }

            $content = null;
            if ($type == \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT && !empty($envelope)) {
                $content = $envelope->getData('array');
            }

            $this->logger->error(
                "Feed send failed. Type: {$type}.",
                [
                    'specifics' => $specifics,
                    'content' => $content,
                    'response' => $response,
                    'path' => __METHOD__,
                ]
            );
        }

        return $response;
    }

    private function createFile($type = 'feed', $name = '_POST_PRODUCT_DATA_', $code = 'var')
    {
        $timestamp = uniqid();
        $path = $this->directory->getPath($code) . DS . 'amazon' . DS . strtolower($type);
        // Check if directory exists
        if (!$this->file->fileExists($path)) {
            $this->file->mkdir($path, 0777, true);
        }

        // File path
        $filePath = $path . DS . strtolower($name) . '-' . $timestamp . '.xml';

        // Check if file exists
        if (!$this->file->fileExists($filePath)) {
            $this->file->write($filePath, '', 0777);
        }

        return $filePath;
    }

    /**
     * Sync feed result
     * @param $id
     * @param \Ced\Amazon\Api\Data\FeedInterface|null $feed
     * @return bool
     */
    public function sync($id, $feed = null)
    {
        $status = false;
        if (isset($id) && !empty($id)) {
            try {
                if (!isset($feed)) {
                    /** @var \Ced\Amazon\Api\Data\FeedInterface $feed */
                    $feed = $this->getById($id);
                }
                $status = $feed->getStatus();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                $account = $this->account->getById($feed->getAccountId());

                /** @var \Amazon\Sdk\Api\Feed\Result $result */
                $result = $this->result->create(
                    [
                        'id' => $feed->getFeedId(),
                        'config' => $account->getConfig(),
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode()
                    ]
                );

                $result->fetchFeedResult();
                $response = $result->getRawFeed();

                if (strpos($response, '<StatusCode>Complete</StatusCode>') !== false) {
                    $status = \Ced\Amazon\Model\Source\Feed\Status::DONE;
                    $feed->setStatus(\Ced\Amazon\Model\Source\Feed\Status::DONE);
                    $this->save($feed);
                }

                $result->saveFeed($feed->getResponseFile());

                $fileInfo = $this->file->getPathInfo($feed->getResponseFile());
                $fileName = $fileInfo['basename'];
                $file = $this->directory->getPath('media') . self::MEDIA_PATH . $fileName;
                if ($this->file->fileExists($file)) {
                    $this->file->cp($feed->getResponseFile(), $file);
                }
            } catch (\Exception $e) {
                $status = false;
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }

        return $status;
    }

    /**
     * Get Feed by Id
     * @param $id
     * @return \Ced\Amazon\Model\Feed
     */
    public function getById($id)
    {
        $feed = $this->modelFactory->create();
        $this->resource->load($feed, $id);
        return $feed;
    }

    /**
     * Save
     * @param \Ced\Amazon\Api\Data\FeedInterface $feed
     * @return int
     * @throws \Exception
     */
    public function save(\Ced\Amazon\Api\Data\FeedInterface $feed)
    {
        $this->resource->save($feed);
        return $feed->getId();
    }

    /**
     * Resend feed and update row
     * @param $id
     * @return bool
     */
    public function resend($id)
    {
        $status = false;
        if (isset($id) && !empty($id)) {
            try {
                /** @var \Ced\Amazon\Api\Data\FeedInterface $feed */
                $feed = $this->getById($id);

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                $account = $this->account->getById($feed->getAccountId());

                if ($this->file->fileExists($feed->getFeedFile())) {
                    $content = $this->file->read($feed->getFeedFile());

                    /**
                     * Sending Feed to Amazon
                     * @var \Amazon\Sdk\Api\Feed $amz
                     */
                    $amz = $this->feed->create(
                        [
                            'config' => $account->getConfig(),
                            'logger' => $this->logger
                        ]
                    );
                    // Feed types listed in documentation
                    $amz->setFeedType($feed->getType());
                    $amz->setFeedContent($content);
                    $amz->submitFeed();
                    $response = $amz->getResponse();
                    $responsePath = $feed->getResponseFile();
                    // Writing response to file
                    if (isset($amz->getRawResponses()[0]['body'])) {
                        $this->file->write($responsePath, $amz->getRawResponses()[0]['body']);
                    }
                    //Update Media file
                    $this->updateMediaFile($responsePath);

                    // Saving in Magento Amazon Feeds
                    $feed->addData([
                        'feed_id' => $response['FeedSubmissionId'],
                        'feed_created_date' => $response['SubmittedDate'],
                        'status' => $response['FeedProcessingStatus'],
                        'response_file' => $responsePath,
                    ]);

                    $this->resource->save($feed);

                    $status = true;
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }

        return $status;
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
     * Get all Data
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\FeedSearchResultsInterface
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

        /** @var \Ced\Amazon\Model\Feed $item */
        foreach ($collection as &$item) {
            $items[$item->getId()] = $item;
        }

        /** @var \Ced\Amazon\Api\Data\FeedSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param \Ced\Amazon\Model\ResourceModel\Feed\Collection $collection
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
            $collection = $this->collectionFactory->create();
        }

        if (isset($created)) {
            $collection->addFieldToFilter(\Ced\Amazon\Model\Feed::COLUMN_CREATED_DATE, ['lteq' => $created]);
        }

        $size = $status = $collection->getSize();
        if (isset($collection) && $size > 0) {
            /** @var \Ced\Amazon\Model\Feed $feed */
            foreach ($collection as $feed) {
                $feedFile = $feed->getFeedFile();
                if ($this->file->fileExists($feedFile)) {
                    $this->file->rm($feedFile);
                }

                $responseFile = $feed->getResponseFile();
                if ($this->file->fileExists($responseFile)) {
                    $this->file->rm($responseFile);
                }

                try {
                    $this->resource->delete($feed);
                    $status = $feed->isDeleted();
                } catch (\Exception $e) {
                    // Silence
                }
            }
        }

        return $status;
    }
}
