<?php

namespace Potato\Crawler\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\Crawler\Api as CrawlerApi;
use Potato\Crawler\Model as CrawlerModel;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class CounterRepository
 */
class CounterRepository implements CrawlerApi\CounterRepositoryInterface
{
    /**
     * @var \Potato\Crawler\Model\CounterFactory
     */
    protected $counterFactory;

    /**
     * @var \Potato\Crawler\Model\CounterRegistry
     */
    protected $counterRegistry;

    /**
     * @var \Potato\Crawler\Api\Data\CounterSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Counter
     */
    protected $counterResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchBuilder;

    /**
     * CounterRepository constructor.
     * @param CrawlerModel\CounterFactory $counterFactory
     * @param CrawlerModel\CounterRegistry $counterRegistry
     * @param CrawlerApi\Data\CounterSearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Counter $counterResource
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        CrawlerModel\CounterFactory $counterFactory,
        CrawlerModel\CounterRegistry $counterRegistry,
        CrawlerApi\Data\CounterSearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Counter $counterResource,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->counterFactory = $counterFactory;
        $this->counterRegistry = $counterRegistry;
        $this->counterResource = $counterResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * @param CrawlerApi\Data\CounterInterface $counter
     * @return $this|CrawlerApi\Data\CounterInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(CrawlerApi\Data\CounterInterface $counter)
    {
        $counterData = $this->extensibleDataObjectConverter->toNestedArray(
            $counter,
            [],
            CrawlerApi\Data\CounterInterface::class
        );


        $counterModel = $this->counterFactory->create();
        $counterModel->addData($counterData);
        $counterModel->setId($counter->getId());
        $this->counterResource->save($counterModel);
        $this->counterRegistry->push($counterModel);
        //$savedObject = $this->get($counterModel->getId());
        return $this;
    }

    /**
     * @param int $counterId
     * @return CrawlerApi\Data\CounterInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($counterId)
    {
        $counterModel = $this->counterRegistry->retrieve($counterId);
        return $counterModel->getDataModel();
    }

    /**
     * @param CrawlerApi\Data\CounterInterface $counter
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(CrawlerApi\Data\CounterInterface $counter)
    {
        return $this->deleteById($counter->getId());
    }
    
    /**
     * @param int $counterId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($counterId)
    {
        $counterModel = $this->counterRegistry->retrieve($counterId);
        $counterModel->getResource()->delete($counterModel);
        $this->counterRegistry->remove($counterId);
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
        $collection = $this->counterFactory->create()->getCollection();
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

        $counters = [];
        foreach ($collection as $counterModel) {
            $counters[] = $counterModel->getDataModel();
        }
        $searchResults->setItems($counters);
        return $searchResults;
    }

    /**
     * Create new empty counter model
     * @return CrawlerApi\Data\CounterInterface
     */
    public function create()
    {
        return $this->counterRegistry->create();
    }

    /**
     * @return \Potato\Crawler\Api\Data\CounterSearchResultsInterface
     */
    public function getListForToday()
    {
        $today = new \DateTime();
        $criteria = $this
            ->searchBuilder
            ->addFilter(
                'date',
                $today->format('Y-m-d'),
                'eq'
            )
            ->create();
        return $this->getList($criteria);
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getByDate($date)
    {
        $counter = $this->counterFactory->create();
        $this->counterResource->load($counter, $date, 'date');
        return $counter;
    }
}