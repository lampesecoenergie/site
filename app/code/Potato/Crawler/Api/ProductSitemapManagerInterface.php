<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface ProductSitemapManagerInterface
{
    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids);

    /**
     * @param bool $value
     * @return $this
     */
    public function setWithCategoryFlag($value);
}