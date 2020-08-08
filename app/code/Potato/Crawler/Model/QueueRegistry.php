<?php

namespace Potato\Crawler\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Potato\Crawler\Api\Data\QueueInterfaceFactory;

/**
 * Class Queue
 */
class QueueRegistry
{
    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var array
     */
    private $queueRegistryById = [];

    /**
     * @var ResourceModel\Queue
     */
    private $queueResource;

    /**
     * @param QueueFactory $queueFactory
     * @param ResourceModel\Queue $queueResource
     */
    public function __construct(
        QueueFactory $queueFactory,
        ResourceModel\Queue $queueResource,
        QueueInterfaceFactory $dataFactory
    ) {
        $this->queueResource = $queueResource;
        $this->queueFactory = $queueFactory;
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param int $queueId
     * @return Queue
     * @throws NoSuchEntityException
     */
    public function retrieve($queueId)
    {
        if (!isset($this->queueRegistryById[$queueId])) {
            /** @var Queue $queue */
            $queue = $this->queueFactory->create();
            $this->queueResource->load($queue, $queueId);
            if (!$queue->getId()) {
                throw NoSuchEntityException::singleField('queueId', $queueId);
            } else {
                $this->queueRegistryById[$queueId] = $queue;
            }
        }
        return $this->queueRegistryById[$queueId];
    }

    /**
     * @param int $queueId
     * @return void
     */
    public function remove($queueId)
    {
        if (isset($this->queueRegistryById[$queueId])) {
            unset($this->queueRegistryById[$queueId]);
        }
    }

    /**
     * @param Queue $queue
     * @return $this
     */
    public function push(Queue $queue)
    {
        $this->queueRegistryById[$queue->getId()] = $queue;
        return $this;
    }

    /**
     * @return \Potato\Crawler\Api\Data\QueueInterface
     */
    public function create()
    {
        return $this->dataFactory->create();
    }
}