<?php

namespace Potato\Crawler\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\Crawler\Api as CrawlerApi;
use Potato\Crawler\Model as CrawlerModel;

/**
 * Class QueueRepository
 */
class QueueRepository implements CrawlerApi\QueueRepositoryInterface
{
    /**
     * @var \Potato\Crawler\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Potato\Crawler\Model\QueueRegistry
     */
    protected $queueRegistry;

    /**
     * @var \Potato\Crawler\Api\Data\QueueSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Queue
     */
    protected $queueResource;

    /**
     * @param CrawlerModel\QueueFactory $queueFactory
     * @param CrawlerModel\QueueRegistry $queueRegistry
     * @param CrawlerApi\Data\QueueSearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Queue $queueResource
     */
    public function __construct(
        CrawlerModel\QueueFactory $queueFactory,
        CrawlerModel\QueueRegistry $queueRegistry,
        CrawlerApi\Data\QueueSearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Queue $queueResource
    ) {
        $this->queueFactory = $queueFactory;
        $this->queueRegistry = $queueRegistry;
        $this->queueResource = $queueResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param CrawlerApi\Data\QueueInterface $queue
     * @return CrawlerApi\Data\QueueInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(CrawlerApi\Data\QueueInterface $queue)
    {
        $queueData = $this->extensibleDataObjectConverter->toNestedArray(
            $queue,
            [],
            CrawlerApi\Data\QueueInterface::class
        );


        $queueModel = $this->queueFactory->create();
        $queueModel->addData($queueData);
        $queueModel->setId($queue->getId());
        $this->queueResource->save($queueModel);
        $this->queueRegistry->push($queueModel);
        $savedObject = $this->get($queueModel->getId());
        return $savedObject;
    }

    /**
     * @param int $queueId
     * @return CrawlerApi\Data\QueueInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($queueId)
    {
        $queueModel = $this->queueRegistry->retrieve($queueId);
        return $queueModel->getDataModel();
    }

    /**
     * @param CrawlerApi\Data\QueueInterface $queue
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(CrawlerApi\Data\QueueInterface $queue)
    {
        return $this->deleteById($queue->getId());
    }
    
    /**
     * @param int $queueId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($queueId)
    {
        $queueModel = $this->queueRegistry->retrieve($queueId);
        $queueModel->getResource()->delete($queueModel);
        $this->queueRegistry->remove($queueId);
        return true;
    }

    /**
     * @param Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->queueFactory->create()->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];

            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var Api\SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $queues = [];
        foreach ($collection as $queueModel) {
            $queues[] = $queueModel->getDataModel();
        }
        $searchResults->setItems($queues);
        return $searchResults;
    }

    /**
     * Create new empty queue model
     * @return CrawlerApi\Data\QueueInterface
     */
    public function create()
    {
        return $this->queueRegistry->create();
    }
    
    /**
     * @return int
     */
    public function getQueueSize()
    {
        /** @var Queue\Collection $collection */
        $collection = $this->queueFactory->create()->getCollection();
        return $collection->getSize();
    }
}