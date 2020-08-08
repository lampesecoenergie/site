<?php
namespace Potato\Crawler\Model\Manager;

use Potato\Crawler\Model\ResourceModel\Sitemap\Catalog\Category as CategorySitemap;

class CategorySitemapManager
{
    protected $ids = null;

    /** @var CategorySitemap  */
    protected $categorySitemap;

    /**
     * CategorySitemapManager constructor.
     * @param CategorySitemap $categorySitemap
     */
    public function __construct(
        CategorySitemap $categorySitemap
    ) {
        $this->categorySitemap = $categorySitemap;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->categorySitemap->addFilterByIds($ids);
        return $this;
    }

    /**
     * @param $store
     * @return array
     */
    public function getList($store)
    {
        return $this->categorySitemap->getCollection($store);
    }
}