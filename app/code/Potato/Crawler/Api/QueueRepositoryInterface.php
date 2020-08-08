<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface QueueRepositoryInterface
{
    /**
     * Create Queue service
     *
     * @param \Potato\Crawler\Api\Data\QueueInterface $queue
     * @return \Potato\Crawler\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Data\QueueInterface $queue);

    /**
     * Get info about Queue by queue id
     *
     * @param int $queueId
     * @return \Potato\Crawler\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($queueId);

    /**
     * Delete queue
     *
     * @param \Potato\Crawler\Api\Data\QueueInterface $queue
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(Data\QueueInterface $queue);
    
    /**
     * Create new empty Queue interface
     *
     * @return \Potato\Crawler\Api\Data\QueueInterface
     */
    public function create();

    /**
     * Return size of queue collection
     * 
     * @return int
     */
    public function getQueueSize();
}