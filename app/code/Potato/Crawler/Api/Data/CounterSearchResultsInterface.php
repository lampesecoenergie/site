<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface CounterSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\Crawler\Api\Data\CounterInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\Crawler\Api\Data\CounterInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}