<?php

namespace Potato\Crawler\Model;

use Potato\Crawler\Api\Data;
use Magento\Framework;

/**
 * Class Queue
 */
class Queue extends Framework\Model\AbstractModel
{
    /**
     * @var \Potato\Crawler\Api\Data\QueueInterfaceFactory
     */
    private $queueDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Queue constructor.
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Queue $resource
     * @param ResourceModel\Queue\Collection $resourceCollection
     * @param Data\QueueInterfaceFactory $queueDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Queue $resource,
        ResourceModel\Queue\Collection $resourceCollection,
        Data\QueueInterfaceFactory $queueDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->queueDataFactory = $queueDataFactory;
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
        $this->_init(ResourceModel\Queue::class);
    }

    /**
     * Retrieve Queue model with data
     *
     * @return \Potato\Crawler\Api\Data\QueueInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->queueDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\QueueInterface::class
        );
        return $dataObject;
    }
}