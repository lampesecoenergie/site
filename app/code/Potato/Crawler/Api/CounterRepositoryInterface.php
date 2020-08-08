<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface CounterRepositoryInterface
{
    /**
     * Create Counter service
     *
     * @param \Potato\Crawler\Api\Data\CounterInterface $counter
     * @return \Potato\Crawler\Api\Data\CounterInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Data\CounterInterface $counter);

    /**
     * Get info about Counter by counter id
     *
     * @param int $counterId
     * @return \Potato\Crawler\Api\Data\CounterInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($counterId);

    /**
     * Delete counter
     *
     * @param \Potato\Crawler\Api\Data\CounterInterface $counter
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(Data\CounterInterface $counter);
    
    /**
     * Create new empty Counter interface
     *
     * @return \Potato\Crawler\Api\Data\CounterInterface
     */
    public function create();

    /**
     * @return \Potato\Crawler\Api\Data\CounterSearchResultsInterface
     */
    public function getListForToday();
}