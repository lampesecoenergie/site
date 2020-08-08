<?php
namespace Potato\Crawler\Model\Manager;

use Potato\Crawler\Model\ResourceModel\Sitemap\Catalog\Product as ProductSitemap;

class ProductSitemapManager
{
    /** @var ProductSitemap  */
    protected $productSitemap;

    /**
     * ProductSitemapManager constructor.
     * @param ProductSitemap $productSitemap
     */
    public function __construct(
        ProductSitemap $productSitemap
    ) {
        $this->productSitemap = $productSitemap;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->productSitemap->addFilterByIds($ids);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setWithCategoryFlag($value)
    {
        $this->productSitemap->setWithCategoryFlag($value);
        return $this;
    }

    /**
     * @param $store
     * @return array
     */
    public function getList($store)
    {
        return $this->productSitemap->getCollection($store);
    }
}