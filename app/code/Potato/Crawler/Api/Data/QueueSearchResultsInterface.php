<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface QueueSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\Crawler\Api\Data\QueueInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\Crawler\Api\Data\QueueInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}