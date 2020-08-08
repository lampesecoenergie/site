<?php

namespace Potato\Crawler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Potato\Crawler\Model\Config;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Protocol
 */
class UrlSource implements OptionSourceInterface
{
    const DATABASE_VALUE  = 1;
    const SITEMAP_VALUE   = 2;

    /** @var Config  */
    protected $config;

    /** @var Url\Sitemap  */
    protected $urlSitemapFactory;

    /** @var Url\Database  */
    protected $urlDatabaseFactory;

    /**
     * UrlSource constructor.
     * @param Config $config
     * @param Url\DatabaseFactory $databaseFactory
     * @param Url\SitemapFactory $sitemapFactory
     */
    public function __construct(
        Config $config,
        Url\DatabaseFactory $databaseFactory,
        Url\SitemapFactory $sitemapFactory
    ) {
        $this->config = $config;
        $this->urlSitemapFactory = $sitemapFactory;
        $this->urlDatabaseFactory = $databaseFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DATABASE_VALUE, 'label' => __('Database')],
            ['value' => self::SITEMAP_VALUE, 'label' => __('Sitemap')]
        ];
    }

    /**
     * @param StoreInterface $store
     * @param null|array $ids
     * @return Url\Database|Url\Sitemap
     */
    public function getInstance($store, $ids = null)
    {
        $source = $this->config->getSource($store);
        if ($source == self::SITEMAP_VALUE && empty($ids)) {
            $path = $this->config->getSourcePath($store);
            return $this->urlSitemapFactory->create()->setPath($path);
        }
        return $this->urlDatabaseFactory->create()->setStore($store);
    }

    public function getDatabaseInstance($store)
    {
        return $this->urlDatabaseFactory->create()->setStore($store);
    }
}