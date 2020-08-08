<?php
namespace Potato\Crawler\Model\Manager;

use Potato\Crawler\Api\QueueManagerInterface;
use Potato\Crawler\Model\Config;
use Potato\Crawler\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\Crawler\Model\ResourceModel\Popularity as Resource;

/**
 * Class QueueManager
 */
class QueueManager implements QueueManagerInterface
{
    const CACHE_QUEUE_ALL_FLAG = 'Potato_Crawler_Model_Observer_Queue::ALL';
    const CACHE_QUEUE_PRODUCT_FLAG = 'Potato_Crawler_Model_Observer_Queue::PRODUCT';
    const CACHE_QUEUE_CATEGORY_FLAG = 'Potato_Crawler_Model_Observer_Queue::CATEGORY';
    const CACHE_QUEUE_CMS_FLAG = 'Potato_Crawler_Model_Observer_Queue::CMS';
    const CACHE_QUEUE_STORES_FLAG = 'Potato_Crawler_Model_Observer_Queue::STORE';

    const CACHE_LIFETIME = 1800;

    /** @var Config  */
    protected $config;
    
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    protected $resource;

    /**
     * QueueManager constructor.
     * @param Config $config
     * @param Logger $logger
     * @param CacheInterface $cache
     * @param Resource $resource
     */
    public function __construct(
        Config $config,
        Logger $logger,
        CacheInterface $cache,
        Resource $resource
    ) {
        $this->config = $config;
        $this->logger = $logger;

        $this->cache = $cache;
        $this->resource = $resource;
    }

    /**
     * @param string $url
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param int $priority
     * @return void
     */
    public function addUrl($url, $store, $priority)
    {
        $basePriority = $this->config->getPriority($store) . $priority;
        //sort by customer group priority
        foreach ($this->config->getCustomerGroup($store) as $groupPriority => $group) {
            //sort by currency priority
            $availableCurrencies = $store->getAvailableCurrencyCodes(true);
            foreach ($this->config->getCurrency($store) as $currencyPriority => $currency) {
                if (!in_array($currency, $availableCurrencies)) {
                    continue;
                }
                //sort by user agent
                foreach ($this->config->getUserAgents($store) as $userAgent) {
                    //calculate priority
                    $priority = $basePriority . $groupPriority . $currencyPriority . $groupPriority;
                    $data = [
                        'store_id'          => $store->getId(),
                        'customer_group_id' => $group,
                        'useragent'         => $userAgent['useragent'],
                        'currency'          => $currency,
                        'priority'          => (int)$priority,
                        'url'               => $url
                    ];
                    try {
                        $this->resource->getConnection()->insertOnDuplicate(
                            $this->resource->getTable('po_crawler_queue'),
                            $data
                        );
                    } catch (\Exception $e) {
                        $this->logger->customError($e);
                    }
                    $this->logger->info('Url "%s" has been added to the queue with %s priority .', [$url, $priority]);
                }
            }
        }
    }

    /**
     * @param $objectId
     * @param $cacheIndex
     * @return $this
     */
    public function addToQueue($objectId, $cacheIndex)
    {
        if (!$objectIds = $this->cache->load($cacheIndex)) {
            $objectIds = serialize([]);
        }
        $objectIds = unserialize($objectIds);
        array_push($objectIds, $objectId);
        $this->cache->save(serialize($objectIds), $cacheIndex, [], self::CACHE_LIFETIME);
        return $this;
    }
}