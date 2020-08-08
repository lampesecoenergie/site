<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface CategorySitemapManagerInterface
{
    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids);
}