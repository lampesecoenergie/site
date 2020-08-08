<?php
namespace Potato\Crawler\Observer\Queue;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Potato\Crawler\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\Crawler\Model\Manager\QueueManager;

/**
 * Class AbstractQueue
 */
abstract class AbstractQueue implements ObserverInterface
{
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    /** @var QueueManager  */
    protected $queueManager;

    /**
     * AbstractQueue constructor.
     * @param Logger $logger
     * @param CacheInterface $cache
     * @param QueueManager $queueManager
     */
    public function __construct(
        Logger $logger,
        CacheInterface $cache,
        QueueManager $queueManager
    ){
        $this->logger = $logger;
        $this->cache = $cache;
        $this->queueManager = $queueManager;
    }
}