<?php
namespace Potato\Crawler\Model;

use Potato\Crawler\Logger\Logger;
use Magento\Framework\App\CacheInterface;

class Warmer
{
    const CUSTOMER_GROUP_ID_COOKIE_NAME = 'po_crawler_group';
    const CURRENCY_COOKIE_NAME = 'po_crawler_currency';
    const STORE_COOKIE_NAME = 'po_crawler_store';
    const CACHE_SPEED_INDEX = 'Potato_Crawler_Helper_Warmer::SPEED';
    const CACHE_LIFETIME = 1800;

    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;

    /** @var CacheInterface  */
    protected $cache;

    /**
     * Warmer constructor.
     * @param Config $config
     * @param Logger $logger
     * @param CacheInterface $cache
     */
    public function __construct(
        Config $config,
        Logger $logger,
        CacheInterface $cache
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Get current load average
     *
     * @return int
     */
    public function getCurrentCpuLoad()
    {
        if ($this->isWin()) {
            return false;
        }

        $cores = $this->getCpuCoresNumber();
        $currentAvg = $this->getCurrentCpuLoadAvg();
        $fullLoad = $cores + $cores/2;
        return min(100, $currentAvg * 100 / $fullLoad );
    }

    /**
     * @return bool
     */
    public function isWin()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getCurrentCpuLoadAvg()
    {
        if (!function_exists('sys_getloadavg')) {
            return 99999;
        }
        try {
            $load = sys_getloadavg();
            return $load[0];
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return 99999;
    }

    /**
     * @return float|int|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAcceptableLoadAverage()
    {
        if ($this->isWin()) {
            return 1.5;
        }
        $fullLoad = $this->getFullLoadValue();
        return $fullLoad * ($this->config->getAcceptableCpu() / 100);
    }

    public function getFullLoadValue()
    {
        $cores = $this->getCpuCoresNumber();
        return $cores + $cores/2;
    }

    /**
     * Get CPU cores count (for UNIX only)
     *
     * @return int|mixed
     */
    public function getCpuCoresNumber()
    {
        $result = [];
        $status = [];
        try {
            exec('grep -c ^processor /proc/cpuinfo 2>&1', $result, $status);
            if ($status != 0) {
                new \Exception(print_r($result, true));
            }
            return $result[0];
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return 1;
    }

    /**
     * @param $urls
     * @param $time
     */
    public function calculateSpeed($urls, $time)
    {
        $this->logger->info('Current Speed %d.', array(($urls / $time) * 3600));
        $this->cache->save(($urls / $time) * 3600, self::CACHE_SPEED_INDEX, [], self::CACHE_LIFETIME);
    }

    /**
     * @return int
     */
    public function getCurrentSpeed()
    {
        return (int)$this->cache->load(self::CACHE_SPEED_INDEX);
    }

    /**
     * Calculate acceptable thread count
     *
     * @return float|int
     */
    public function getThreadCount()
    {
        if ($this->isWin()) {
            //apache will crashed if $threads > 1
            return 1;
        }
        $currentAvr = $this->getCurrentCpuLoadAvg();
        $thread = round($this->getAcceptableLoadAverage() - $currentAvr);
        return $thread > 0 ? $thread : 0;
    }
}