<?php
namespace Potato\Crawler\Observer\Queue;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Potato\Crawler\Model\Manager\QueueManager;

/**
 * Class AddStoreToQueue
 */
class AddStoreToQueue extends AbstractQueue implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $stores = $observer->getStores();
        foreach ($stores as $store) {
            try {
                $this->queueManager->addToQueue($store->getId(), QueueManager::CACHE_QUEUE_STORES_FLAG);
            } catch (\Exception $e) {
                $this->logger->customError($e);
            }
        }
    }
}