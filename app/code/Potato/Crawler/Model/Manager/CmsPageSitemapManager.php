<?php
namespace Potato\Crawler\Model\Manager;

use Potato\Crawler\Model\ResourceModel\Sitemap\Cms\Page as CmsPageSitemap;

class CmsPageSitemapManager
{
    /** @var CmsPageSitemap  */
    protected $cmsPageSitemap;

    /**
     * CmsPageSitemapManager constructor.
     * @param CmsPageSitemap $cmsPageSitemap
     */
    public function __construct(
        CmsPageSitemap $cmsPageSitemap
    ) {
        $this->cmsPageSitemap = $cmsPageSitemap;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->cmsPageSitemap->addFilterByIds($ids);
        return $this;
    }

    /**
     * @param $store
     * @return array
     */
    public function getList($store)
    {
        return $this->cmsPageSitemap->getCollection($store);
    }
}