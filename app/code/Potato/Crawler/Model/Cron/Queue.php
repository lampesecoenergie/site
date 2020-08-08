<?php
namespace Potato\Crawler\Model\Cron;

use Potato\Crawler\Model\Config;
use Potato\Crawler\Logger\Logger;
use Potato\Crawler\Model\Source\UrlSource;
use Potato\Crawler\Model\Source\Url\Database as UrlDatabase;
use Potato\Crawler\Model\Lock\Queue as Lock;
use Potato\Crawler\Model\Source\PageGroup;
use Potato\Crawler\Api\QueueManagerInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\CacheInterface;
use Potato\Crawler\Model\Manager\QueueManager;
use Potato\Crawler\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

class Queue
{
    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;
        
    /** @var UrlSource  */
    protected $urlSource;
    
    /** @var QueueManagerInterface  */
    protected $queueManager;

    /** @var Schedule  */
    protected $cron;

    /** @var CacheInterface  */
    protected $cache;

    /** @var Lock  */
    protected $lock;

    protected $queueCollectionFactory;

    /**
     * Queue constructor.
     * @param Config $config
     * @param Logger $logger
     * @param UrlSource $urlSource
     * @param QueueManagerInterface $queueManager
     * @param Schedule $cron
     * @param CacheInterface $cache
     * @param Lock $lock
     */
    public function __construct(
        Config $config,
        Logger $logger,
        UrlSource $urlSource,
        QueueManagerInterface $queueManager,
        Schedule $cron,
        CacheInterface $cache,
        QueueCollectionFactory $queueCollectionFactory,
        Lock $lock
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->queueManager = $queueManager;
        $this->urlSource = $urlSource;
        $this->cron = $cron;
        $this->cache = $cache;
        $this->lock = $lock;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * @return $this
     */
    public function process()
    {
        if (!$this->config->isEnabled() || $this->lock->isLocked()) {
            $this->logger->info('Warmer Queue already running PID  %s.', array($this->lock->getProcessPid()));
            return $this;
        }
        try {
            //add website urls to queue
            foreach ($this->config->getStoresQueue() as $store) {
                $this->addStoreUrls($store);
            }
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        $this->lock->removeLock();
        return $this;
    }

    /**
     * Add to queue specified urls
     *
     * @param array $ids
     * @return $this
     */
    public function addSpecified($ids)
    {
        if (empty($ids)) {
            return $this;
        }
        try {
            foreach ($this->config->getStoresQueue() as $store) {
                $this->addStoreUrls($store, $ids);
            }
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return $this;
    }

    /**
     * Add store urls
     *
     * @param $store
     * @param null|array $ids
     * @return $this
     */
    public function addStoreUrls($store, $ids = null)
    {
        if (!$this->config->isEnabled($store)) {
            return $this;
        }
        //get store urls source
        if ($ids) {
            $source = $this->urlSource->getDatabaseInstance($store);
        } else {
            $source = $this->urlSource->getInstance($store, $ids);
        }

        if ($source instanceof UrlDatabase) {
            return $this->addFromDatabase($source, $store, $ids);
        }
        return $this->addFromSitemap($source, $store);
    }

    /**
     * Add from database source
     *
     * @param $source
     * @param $store
     * @param array $ids
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function addFromDatabase($source, $store, $ids = [])
    {
        //sort by page priority
        foreach ($this->config->getPages($store) as $pagePriority => $type) {

            $this->lock->updateLockFile();

            if ($type == PageGroup::CMS_VALUE) {
                $cmsIds = isset($ids['cms']) ? $ids['cms'] : [];
                $urls = $source->getCmsUrls($cmsIds);
            } elseif ($type == PageGroup::CATEGORY_VALUE) {
                $categoryIds = isset($ids['category']) ? $ids['category'] : [];
                $urls = $source->getCategoryUrls($categoryIds);
            } else {
                $productIds = isset($ids['product']) ? $ids['product'] : [];
                $urls = $source->getProductUrls($productIds);
            }
            //sort by protocol
            foreach ($this->config->getProtocol($store) as $protocolPriority => $protocol) {
                $baseUrl = $this->config->getStoreBaseUrl($store, $protocol);
                foreach ($urls as $url) {
                    $this->queueManager->addUrl(htmlspecialchars($baseUrl . $url), $store, $pagePriority . $protocolPriority);
                }
            }
        }
        return $this;
    }

    /**
     * Add from sitemap
     * @param $source
     * @param $store
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function addFromSitemap($source, $store)
    {
        $urls = $source->getStoreUrls();
        foreach ($urls as $priority => $group) {

            $this->lock->updateLockFile();

            foreach ($group as $url) {
                $this->queueManager->addUrl($url, $store, $priority);
            }
        }
        return $this;
    }

    /**
     * Warmup via cron
     *
     * @return $this
     */
    public function cronProcess()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        $collection = $this->queueCollectionFactory->create();
        if ($collection->getSize() > 0) {
            return $this;
        }
        $this->logger->info('Warmup via cron.');
        $this->cache->save(true, QueueManager::CACHE_QUEUE_ALL_FLAG, [], QueueManager::CACHE_LIFETIME);
        return $this;
    }
}