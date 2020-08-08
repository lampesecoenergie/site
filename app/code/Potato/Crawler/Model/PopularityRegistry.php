<?php

namespace Potato\Crawler\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Potato\Crawler\Api\Data\PopularityInterfaceFactory;

/**
 * Class Popularity
 */
class PopularityRegistry
{
    /**
     * @var PopularityFactory
     */
    private $popularityFactory;

    /**
     * @var array
     */
    private $popularityRegistryById = [];

    /**
     * @var array
     */
    private $popularityRegistryByUrl = [];

    /**
     * @var ResourceModel\Popularity
     */
    private $popularityResource;

    /**
     * PopularityRegistry constructor.
     * @param PopularityFactory $popularityFactory
     * @param ResourceModel\Popularity $popularityResource
     * @param PopularityInterfaceFactory $dataFactory
     */
    public function __construct(
        PopularityFactory $popularityFactory,
        ResourceModel\Popularity $popularityResource,
        PopularityInterfaceFactory $dataFactory
    ) {
        $this->popularityResource = $popularityResource;
        $this->popularityFactory = $popularityFactory;
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param int $popularityId
     * @return Popularity
     * @throws NoSuchEntityException
     */
    public function retrieve($popularityId)
    {
        if (!isset($this->popularityRegistryById[$popularityId])) {
            /** @var Popularity $popularity */
            $popularity = $this->popularityFactory->create();
            $this->popularityResource->load($popularity, $popularityId);
            if (!$popularity->getId()) {
                throw NoSuchEntityException::singleField('popularityId', $popularityId);
            } else {
                $this->popularityRegistryById[$popularityId] = $popularity;
            }
        }
        return $this->popularityRegistryById[$popularityId];
    }
    
    /**
     * @param string $url
     * @return Popularity
     * @throws NoSuchEntityException
     */
    public function retrieveByUrl($url)
    {
        if (!isset($this->popularityRegistryByUrl[$url])) {
            /** @var Popularity $popularity */
            $popularity = $this->popularityFactory->create();
            $this->popularityResource->load($popularity, $url, 'url');
            if (!$popularity->getId()) {
                throw NoSuchEntityException::singleField('url', $url);
            } else {
                $this->popularityRegistryByUrl[$url] = $popularity;
            }
        }
        return $this->popularityRegistryByUrl[$url];
    }

    /**
     * @param int $popularityId
     * @return void
     */
    public function remove($popularityId)
    {
        if (isset($this->popularityRegistryById[$popularityId])) {
            unset($this->popularityRegistryById[$popularityId]);
        }
    }

    /**
     * @param string $url
     * @return void
     */
    public function removeFromUrl($url)
    {
        if (isset($this->popularityRegistryByUrl[$url])) {
            unset($this->popularityRegistryByUrl[$url]);
        }
    }

    /**
     * @param Popularity $popularity
     * @return $this
     */
    public function push(Popularity $popularity)
    {
        $this->popularityRegistryById[$popularity->getId()] = $popularity;
        return $this;
    }

    /**
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     */
    public function create()
    {
        return $this->dataFactory->create();
    }
}