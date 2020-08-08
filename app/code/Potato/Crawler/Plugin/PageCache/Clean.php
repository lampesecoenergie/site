<?php

namespace Potato\Crawler\Plugin\PageCache;

use Magento\PageCache\Model\Cache\Type as CacheType;
use Potato\Crawler\Model\Manager\QueueManager;
use Potato\Crawler\Logger\Logger;
use Magento\Framework\App\CacheInterface;

/**
 * Class Clean
 */
class Clean
{
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    /** @var QueueManager  */
    protected $queueManager;

    /** @var \Potato\Crawler\Model\Config  */
    protected $config;

    const CMS_TAG_REGEX = '/cms_p_(\d+)/';

    const CATEGORY_TAG_REGEX = '/cat_c_(\d+)/';

    const PRODUCT_TAG_REGEX = '/cat_p_(\d+)/';

    /**
     * Clean constructor.
     * @param Logger $logger
     * @param CacheInterface $cache
     * @param QueueManager $queueManager
     * @param \Potato\Crawler\Model\Config $config
     */
    public function __construct(
        Logger $logger,
        CacheInterface $cache,
        QueueManager $queueManager,
        \Potato\Crawler\Model\Config $config
    ) {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;

        $this->queueManager = $queueManager;
    }

    /**
     * @param CacheType $subject
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterClean(CacheType $subject)
    {
        if (!$this->config->isEnabled()) {
            return true;
        }
        if (func_num_args() >= 3 && func_get_arg(2) === 'matchingAnyTag'
            && is_array(func_get_arg(3))
            && !empty(func_get_arg(3))
        ) {
            foreach (func_get_arg(3) as $tag) {
                $this->logger->info('Flushing cache by tag %s has been registered.', [$tag]);
                try {
                    $this->_addToQueueByTag($tag);
                } catch (\Exception $e) {
                    $this->logger->customError($e);
                }
            }
            return true;
        } else {
            $this->logger->info('Flushing full page cache has been registered.');
            $this->cache->save(true, QueueManager::CACHE_QUEUE_ALL_FLAG, [], QueueManager::CACHE_LIFETIME);
        }
        return true;
    }

    /**
     * @param string $tag
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addToQueueByTag($tag)
    {
        preg_match(self::CMS_TAG_REGEX, $tag, $match);
        if ($match && !empty($match)) {
            $this->queueManager->addToQueue($match[1], QueueManager::CACHE_QUEUE_CMS_FLAG);
            return $this;
        }
        preg_match(self::CATEGORY_TAG_REGEX, $tag, $match);
        if ($match && !empty($match)) {
            $this->queueManager->addToQueue($match[1], QueueManager::CACHE_QUEUE_CATEGORY_FLAG);
            return $this;
        }
        preg_match(self::PRODUCT_TAG_REGEX, $tag, $match);
        if ($match && !empty($match)) {
            $this->queueManager->addToQueue($match[1], QueueManager::CACHE_QUEUE_PRODUCT_FLAG);
        }
        return $this;
    }
}