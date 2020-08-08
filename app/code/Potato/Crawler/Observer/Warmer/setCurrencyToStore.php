<?php
namespace Potato\Crawler\Observer\Warmer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Potato\Crawler\Model\Warmer as WarmerModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class setCurrencyToStore
 */
class setCurrencyToStore implements ObserverInterface
{
    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * setCurrencyToStore constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (isset($_COOKIE[WarmerModel::STORE_COOKIE_NAME])) {
            $this->storeManager->setCurrentStore($_COOKIE[WarmerModel::STORE_COOKIE_NAME]);
        }
        if (isset($_COOKIE[WarmerModel::CURRENCY_COOKIE_NAME])) {
            $this->storeManager->getStore()->setCurrentCurrencyCode($_COOKIE[WarmerModel::CURRENCY_COOKIE_NAME]);
        }
    }
}