<?php

namespace Potato\Crawler\Model\Source\Url;

use Magento\Store\Api\Data\StoreInterface;
use Potato\Crawler\Api\ProductSitemapManagerInterface;
use Potato\Crawler\Api\CategorySitemapManagerInterface;
use Potato\Crawler\Api\CmsPageSitemapManagerInterface;
use Potato\Crawler\Model\Config;

/**
 * Class Datebase
 */
class Database
{
    /**
     * @var StoreInterface null
     */
    protected $store = null;

    /** @var ProductSitemapManagerInterface  */
    protected $productSitemapManager;
    
    /** @var CategorySitemapManagerInterface  */
    protected $categorySitemapManager;
    
    /** @var CmsPageSitemapManagerInterface  */
    protected $cmsPageSitemapManager;
    
    /** @var Config  */
    protected $config;

    /**
     * Database constructor.
     * @param Config $config
     * @param ProductSitemapManagerInterface $productSitemapManager
     * @param CategorySitemapManagerInterface $categorySitemapManager
     * @param CmsPageSitemapManagerInterface $cmsPageSitemapManager
     */
    public function __construct(
        Config $config,
        ProductSitemapManagerInterface $productSitemapManager,
        CategorySitemapManagerInterface $categorySitemapManager,
        CmsPageSitemapManagerInterface $cmsPageSitemapManager
    ) {
        $this->productSitemapManager = $productSitemapManager;
        $this->categorySitemapManager = $categorySitemapManager;
        $this->cmsPageSitemapManager = $cmsPageSitemapManager;
        $this->config = $config;
    }

    /**
     * Prepare product urls
     *
     * @param array $ids
     * @return array
     */
    public function getProductUrls($ids = [])
    {
        $result = [];

        if (!empty($ids)) {
            $this->productSitemapManager->addFilterByIds($ids);
        }
        if (!$this->config->useShortProductUrls($this->store)) {
            $this->productSitemapManager->setWithCategoryFlag(true);
        }
        $products = $this->productSitemapManager->getCollection($this->store);
        
        foreach ($products as $product) {
            $result[] = $product->getUrl();
        }
        unset($products);
        return $result;
    }

    /**
     * Prepare category urls
     *
     * @param array $ids
     * @return array
     */
    public function getCategoryUrls($ids = [])
    {
        $result = [];
        if (!empty($ids)) {
            $this->categorySitemapManager->addFilterByIds($ids);
        }
        $categories = $this->categorySitemapManager->getCollection($this->store);
        
        foreach ($categories as $category) {
            $result[] = $category->getUrl();
        }
        unset($categories);
        return $result;
    }

    /**
     * Prepare CMS urls
     *
     * @param array $ids
     * @return array
     */
    public function getCmsUrls($ids = [])
    {
        $result = [];
        
        if (!empty($ids)) {
            $this->cmsPageSitemapManager->addFilterByIds($ids);
        }
        $cms = $this->cmsPageSitemapManager->getCollection($this->store);
        foreach ($cms as $item) {
            $result[] = $item->getUrl();
        }
        unset($cms);
        return $result;
    }

    /**
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store)
    {
        $this->store = $store;
        return $this;
    }
}