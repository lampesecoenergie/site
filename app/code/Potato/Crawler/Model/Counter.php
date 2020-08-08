<?php

namespace Potato\Crawler\Model;

use Potato\Crawler\Api\Data;
use Magento\Framework;

/**
 * Class Counter
 */
class Counter extends Framework\Model\AbstractModel
{
    /**
     * @var \Potato\Crawler\Api\Data\CounterInterfaceFactory
     */
    private $counterDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Counter constructor.
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Counter $resource
     * @param ResourceModel\Counter\Collection $resourceCollection
     * @param Data\CounterInterfaceFactory $counterDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Counter $resource,
        ResourceModel\Counter\Collection $resourceCollection,
        Data\CounterInterfaceFactory $counterDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->counterDataFactory = $counterDataFactory;
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
        $this->_init(ResourceModel\Counter::class);
    }

    /**
     * Retrieve Counter model with data
     *
     * @return \Potato\Crawler\Api\Data\CounterInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->counterDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\CounterInterface::class
        );
        return $dataObject;
    }
}