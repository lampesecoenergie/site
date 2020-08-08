<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface CmsPageSitemapManagerInterface
{
    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids);
}