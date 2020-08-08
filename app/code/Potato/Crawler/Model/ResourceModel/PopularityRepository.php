<?php

namespace Potato\Crawler\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\Crawler\Api as CrawlerApi;
use Potato\Crawler\Model as CrawlerModel;

/**
 * Class PopularityRepository
 */
class PopularityRepository implements CrawlerApi\PopularityRepositoryInterface
{
    /**
     * @var \Potato\Crawler\Model\PopularityFactory
     */
    protected $popularityFactory;

    /**
     * @var \Potato\Crawler\Model\PopularityRegistry
     */
    protected $popularityRegistry;

    /**
     * @var \Potato\Crawler\Api\Data\PopularitySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Popularity
     */
    protected $popularityResource;

    /**
     * @param CrawlerModel\PopularityFactory $popularityFactory
     * @param CrawlerModel\PopularityRegistry $popularityRegistry
     * @param CrawlerApi\Data\PopularitySearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Popularity $popularityResource
     */
    public function __construct(
        CrawlerModel\PopularityFactory $popularityFactory,
        CrawlerModel\PopularityRegistry $popularityRegistry,
        CrawlerApi\Data\PopularitySearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Popularity $popularityResource
    ) {
        $this->popularityFactory = $popularityFactory;
        $this->popularityRegistry = $popularityRegistry;
        $this->popularityResource = $popularityResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param CrawlerApi\Data\PopularityInterface $popularity
     * @return CrawlerApi\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(CrawlerApi\Data\PopularityInterface $popularity)
    {
        $popularityData = $this->extensibleDataObjectConverter->toNestedArray(
            $popularity,
            [],
            CrawlerApi\Data\PopularityInterface::class
        );


        $popularityModel = $this->popularityFactory->create();
        $popularityModel->addData($popularityData);
        $popularityModel->setId($popularity->getId());
        $this->popularityResource->save($popularityModel);
        $this->popularityRegistry->push($popularityModel);
        $savedObject = $this->get($popularityModel->getId());
        return $savedObject;
    }

    /**
     * @param int $popularityId
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($popularityId)
    {
        $popularityModel = $this->popularityRegistry->retrieve($popularityId);
        return $popularityModel->getDataModel();
    }

    /**
     * @param string $url
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByUrl($url)
    {
        $popularityModel = $this->popularityRegistry->retrieveByUrl($url);
        return $popularityModel->getDataModel();
    }

    /**
     * @param CrawlerApi\Data\PopularityInterface $popularity
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(CrawlerApi\Data\PopularityInterface $popularity)
    {
        return $this->deleteById($popularity->getId());
    }
    
    /**
     * @param int $popularityId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($popularityId)
    {
        /** @var CrawlerApi\Data\PopularityInterface $popularityModel */
        $popularityModel = $this->popularityRegistry->retrieve($popularityId);
        $popularityModel->getResource()->delete($popularityModel);
        $this->popularityRegistry->remove($popularityId);
        $this->popularityRegistry->removeFromUrl($popularityModel->getUrl());
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
        $collection = $this->popularityFactory->create()->getCollection();
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

        $popularitys = [];
        foreach ($collection as $popularityModel) {
            $popularitys[] = $popularityModel->getDataModel();
        }
        $searchResults->setItems($popularitys);
        return $searchResults;
    }

    /**
     * Create new empty popularity model
     * @return CrawlerApi\Data\PopularityInterface
     */
    public function create()
    {
        return $this->popularityRegistry->create();
    }
}