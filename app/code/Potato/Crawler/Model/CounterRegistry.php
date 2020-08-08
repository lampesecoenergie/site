<?php

namespace Potato\Crawler\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Potato\Crawler\Api\Data\CounterInterfaceFactory;

/**
 * Class Counter
 */
class CounterRegistry
{
    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var array
     */
    private $counterRegistryById = [];

    /**
     * @var ResourceModel\Counter
     */
    private $counterResource;

    /**
     * @param CounterFactory $counterFactory
     * @param ResourceModel\Counter $counterResource
     */
    public function __construct(
        CounterFactory $counterFactory,
        ResourceModel\Counter $counterResource,
        CounterInterfaceFactory $dataFactory
    ) {
        $this->counterResource = $counterResource;
        $this->counterFactory = $counterFactory;
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param int $counterId
     * @return Counter
     * @throws NoSuchEntityException
     */
    public function retrieve($counterId)
    {
        if (!isset($this->counterRegistryById[$counterId])) {
            /** @var Counter $counter */
            $counter = $this->counterFactory->create();
            $this->counterResource->load($counter, $counterId);
            if (!$counter->getId()) {
                throw NoSuchEntityException::singleField('counterId', $counterId);
            } else {
                $this->counterRegistryById[$counterId] = $counter;
            }
        }
        return $this->counterRegistryById[$counterId];
    }

    /**
     * @param int $counterId
     * @return void
     */
    public function remove($counterId)
    {
        if (isset($this->counterRegistryById[$counterId])) {
            unset($this->counterRegistryById[$counterId]);
        }
    }

    /**
     * @param Counter $counter
     * @return $this
     */
    public function push(Counter $counter)
    {
        $this->counterRegistryById[$counter->getId()] = $counter;
        return $this;
    }

    /**
     * @return \Potato\Crawler\Api\Data\CounterInterface
     */
    public function create()
    {
        return $this->dataFactory->create();
    }
}