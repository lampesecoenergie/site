<?php

namespace Potato\Crawler\Model;

use Potato\Crawler\Api\Data;
use Magento\Framework;

/**
 * Class Popularity
 */
class Popularity extends Framework\Model\AbstractModel
{
    /**
     * @var \Potato\Crawler\Api\Data\PopularityInterfaceFactory
     */
    private $popularityDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Popularity constructor.
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Popularity $resource
     * @param ResourceModel\Popularity\Collection $resourceCollection
     * @param Data\PopularityInterfaceFactory $popularityDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Popularity $resource,
        ResourceModel\Popularity\Collection $resourceCollection,
        Data\PopularityInterfaceFactory $popularityDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->popularityDataFactory = $popularityDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Popularity::class);
    }

    /**
     * Retrieve Popularity model with data
     *
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->popularityDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\PopularityInterface::class
        );
        return $dataObject;
    }
}