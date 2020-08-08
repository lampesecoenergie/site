<?php
namespace Potato\Crawler\Model\Cron;

use Magento\Framework\DataObject;
use Potato\Crawler\Model\Config;
use Potato\Crawler\Model\Warmer as WarmerModel;
use Potato\Crawler\Model\Lock\Warmer as Lock;
use Potato\Crawler\Logger\Logger;
use Potato\Crawler\Model\Source\UrlSource;
use Potato\Crawler\Api\QueueManagerInterface;
use Potato\Crawler\Api\CounterRepositoryInterface;
use Potato\Crawler\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

class Warmer
{
    const WAITING_TIMEOUT = 30;

    protected $curl = null;
    protected $acceptableCpu = null;

    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;

    /** @var UrlSource  */
    protected $urlSource;

    /** @var QueueManagerInterface  */
    protected $queueManager;

    /** @var CounterRepositoryInterface  */
    protected $counterRepository;
    
    /** @var QueueCollectionFactory  */
    protected $queueCollectionFactory;

    /** @var WarmerModel  */
    protected $warmer;

    /** @var Lock  */
    protected $lock;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    private $serializer = null;

    /**
     * Warmer constructor.
     * @param Config $config
     * @param Logger $logger
     * @param UrlSource $urlSource
     * @param QueueManagerInterface $queueManager
     * @param CounterRepositoryInterface $counterRepository
     * @param WarmerModel $warmer
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Lock $lock
     */
    public function __construct(
        Config $config,
        Logger $logger,
        UrlSource $urlSource,
        QueueManagerInterface $queueManager,
        CounterRepositoryInterface $counterRepository,
        WarmerModel $warmer,
        QueueCollectionFactory $queueCollectionFactory,
        StoreManagerInterface $storeManager,
        Lock $lock
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->queueManager = $queueManager;
        $this->urlSource = $urlSource;
        $this->warmer = $warmer;
        $this->counterRepository = $counterRepository;
        $this->queueCollectionFactory = $queueCollectionFactory;
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
        $this->storeManager = $storeManager;
        $this->lock = $lock;
    }

    /**
     * @return $this
     */
    public function process()
    {
        if (!$this->config->isEnabled() || $this->lock->isLocked()) {
            $this->logger->info('Warmer already running PID  %s.', array($this->lock->getProcessPid()));
            return $this;
        }
        $this->acceptableCpu = $this->warmer->getAcceptableLoadAverage();
        $this->logger->info('Acceptable CPU load %s.', array($this->acceptableCpu));
        /** @var \Potato\Crawler\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->queueCollectionFactory->create();
        $collection
            ->joinPopularity()
        ;
        try {
            $this->doRequests($collection);
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        $this->lock->removeLock();
        return $this;
    }

    /**
     * @param \Potato\Crawler\Model\ResourceModel\Queue\Collection $collection
     * @return $this
     */
    protected function doRequests($collection)
    {
        $urls = [];
        $threads = 1;
        $options = [];

        while ($item = $collection->fetchItem()) {
            if (!$this->config->isEnabled()) {
                return $this;
            }
            /**
             * Prepare crawler options
             */
            $options = [
                CURLOPT_USERAGENT      => $item->getUseragent(),
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_NOBODY         => true,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_FAILONERROR    => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIE         => $this->getCookie($item)
            ];

            if (!$this->warmer->isWin()) {
                while (!$threads = $this->getThreadCount()) {
                    //wait while cpu overload
                    sleep(self::WAITING_TIMEOUT);
                }
            }
            //prepare options hash
            $hash = md5(implode(',', $options));
            if (isset($prevHash) && isset($prevOptions) && $hash != $prevHash) {
                //if current option hach != current hash do request with prev options
                $this->multiRequest($urls, $prevOptions);
                //reset urls container and thread value
                $urls = [];
                $threads = $this->getThreadCount();
            }

            $prevHash = $hash;
            $prevOptions = $options;

            $urls[] = $item->getUrl();

            //value of $threads is floating
            if (count($urls) >= $threads) {

                $urlsForRequest = $urls;
                if ($urlsForRequest > $threads) {
                    //if urls for request > threads
                    $urlsForRequest = array_slice($urlsForRequest, 0, $threads);
                    $urls = array_diff($urls, $urlsForRequest);
                } else {
                    $urls = [];
                }

                //if count prepared urls = thread value -> do request
                $this->multiRequest($urlsForRequest, $options);
                //reset urls container and thread value

                $threads = $this->getThreadCount();
            }

            try {
                //remove url from queue
                $collection->getResource()->delete($item);
            } catch (\Exception $e) {
                $this->logger->customError($e);
            }
        }

        if (!empty($urls)) {
            //if url container not empty -> do request
            $this->multiRequest($urls, $options);
        }
        return $this;
    }

    /**
     * Do curl request and calculate warmer speed
     *
     * @param $urls
     * @param $options
     * @return $this
     */
    protected function multiRequest($urls, $options)
    {
        $this->lock->updateLockFile();

        $timeBefore = time();
        $result = $this->getCurl()->multiRequest($urls, $options);
        foreach ($result as $page) {
            if (!array_key_exists('content', $page)) {
                continue;
            }
            $vary = $this->getVaryFromResponse($page['content']);

            $this->multiVaryRequest($page['url'], $vary , $options);

        }
        $this->logger->info('Urls "%s" have been requested with options %s.', [implode(",", $urls), $options[CURLOPT_COOKIE]]);
        $timeAfter = time();

        $this->warmer->calculateSpeed(count($urls), max($timeAfter - $timeBefore, 1));

        try {
            $date = new \DateTime();
            $date = $date->format('Y-m-d');
            $counter = $this->counterRepository->getByDate($date);
            $counter->setDate($date);
            $counter->setValue($counter->getValue() + count($urls));
            $this->counterRepository->save($counter->getDataModel());
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return $this;
    }

    protected function multiVaryRequest($url, $vary, $options)
    {
        if (!$vary) {
            return;
        }
        $curlCookie = $options[CURLOPT_COOKIE];
        $curlCookie .= \Magento\Framework\App\Response\Http::COOKIE_VARY_STRING . '=' . $vary . ';';
        $options[CURLOPT_COOKIE] = $curlCookie;
        $this->getCurl()->multiRequest([$url], $options);
    }

    protected function getVaryFromResponse($page)
    {
        if (false === strpos($page, 'Set-Cookie') || false === strpos($page, 'X-Magento-Vary')) {
            return null;
        }
        $vary = null;
        $headers = explode("\r\n", $page);
        foreach ($headers as $header) {
            if (false === strpos($header, 'Set-Cookie') && false === strpos($header, 'X-Magento-Vary')) {
                continue;
            }
            $cookieString = substr($header, strpos($header, 'X-Magento-Vary'));
            $cookieList = explode(";", $cookieString);
            foreach ($cookieList as $cookie) {
                if (false === strpos($cookie, 'X-Magento-Vary')) {
                    continue;
                }
                $vary = trim(str_replace('X-Magento-Vary=', '', $cookie));
                break;
            }
            break;
        }
        return $vary;
    }

    protected function _updateLockFile()
    {

    }

    /**
     * @return null|\Potato\Crawler\Model\Curl
     */
    protected function getCurl()
    {
        if (null === $this->curl) {
            $this->curl = new \Potato\Crawler\Model\Curl();
        }
        return $this->curl;
    }

    /**
     * Prepare cookie for curl request
     *
     * @param DataObject $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCookie($item)
    {
        /**
         * see logic Magento\Framework\App\Http\Context
         */
        $data = [];
        if ($item->getCustomerGroupId()) {
            $data = [
                'customer_group'     => $item->getCustomerGroupId(),
                'customer_logged_in' => true,
            ];
        }
        if ($item->getStoreId() != $this->storeManager->getDefaultStoreView()->getId()) {
            $data['store'] = $this->storeManager->getStore($item->getStoreId())->getCode();
        }
        if ($item->getCurrency() != $this->storeManager->getDefaultStoreView()->getDefaultCurrencyCode()) {
            $data['current_currency'] = $item->getCurrency();
        }
        if (empty($data)) {
            //for guest no cookie with default store and currency
            return '';
        }

        return WarmerModel::CUSTOMER_GROUP_ID_COOKIE_NAME . '=' . $item->getCustomerGroupId() . ';'
            . WarmerModel::STORE_COOKIE_NAME  . '=' . $item->getStoreId() . ';'
            . WarmerModel::CURRENCY_COOKIE_NAME  . '=' . $item->getCurrency() . ';path=/;';
    }

    /**
     * Calculate acceptable thread count
     *
     * @return float|int
     */
    protected function getThreadCount()
    {
        if ($this->warmer->isWin()) {
            //apache will crashed if $threads > 1
            return 1;
        }
        $currentAvr = $this->warmer->getCurrentCpuLoadAvg();
        $thread = round($this->acceptableCpu - $currentAvr);
        $this->logger->info('Current CPU load %s.', array($currentAvr));
        $this->logger->info('Thread %d.', array($thread));
        return $thread > 0 ? $thread : 0;
    }
}