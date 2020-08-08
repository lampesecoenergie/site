<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface PopularitySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\Crawler\Api\Data\PopularityInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\Crawler\Api\Data\PopularityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}