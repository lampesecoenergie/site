<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Cron\Order;

use Ced\Amazon\Api\AccountRepositoryInterface;
use Ced\Amazon\Api\Data\Order\Import\ParamsInterfaceFactory;
use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Service\OrderServiceInterface;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Order;
use Ced\Amazon\Model\ResourceModel\Order\CollectionFactory as AmazonOrderCollectionFactory;
use Ced\Amazon\Service\Order\Sync as OrderSyncService;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Import
{
    const FLAG_FILENAME = '.amazon.order.lock';

    /**
     * Maintenance flag dir
     */
    const FLAG_DIR = DirectoryList::VAR_DIR;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Ced\Amazon\Helper\Order  */
    public $order;

    /** @var \Ced\Amazon\Service\Order  */
    public $service;

    /** @var OrderSyncService  */
    public $sync;

    /** @var ParamsInterfaceFactory  */
    public $paramsFactory;

    /** @var AmazonOrderCollectionFactory  */
    public $amazonOrderCollectionFactory;

    /** @var AccountRepositoryInterface  */
    public $accountRepository;

    /**
     * Path to store files
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    public $flagDir;

    public $result = '';

    public $accountIds = [];

    public $orderId = null;

    public $fetchModified = true;

    public $fetchCreated = true;

    /** @var string, Lower Date  */
    public $lower = null;

    /** @var string, Upper Date  */
    public $upper = null;

    public $medium = "cron";

    public $status = [
        \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
        \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
    ];

    public function __construct(
        Filesystem $filesystem,
        AmazonOrderCollectionFactory $amazonOrderCollectionFactory,
        AccountRepositoryInterface $accountRepository,
        ParamsInterfaceFactory $paramsFactory,
        Order $order,
        OrderServiceInterface $service,
        OrderSyncService $sync,
        ConfigServiceInterface $config,
        Logger $logger
    ) {
        $this->flagDir = $filesystem->getDirectoryWrite(self::FLAG_DIR);

        $this->accountRepository = $accountRepository;
        $this->amazonOrderCollectionFactory = $amazonOrderCollectionFactory;
        $this->paramsFactory = $paramsFactory;
        $this->service = $service;
        $this->order = $order;
        $this->sync = $sync;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        $unlocked = false;
        $sync = false;
        $created = false;
        $modified = false;
        try {
            $sync = (boolean)$this->config->getOrderImport();
            $unlocked = $this->status();
            $tstart = microtime(true);

            if ($sync && $unlocked) {
                $this->lock();
                $this->status = $this->config->getOrderStatus();
                if ($this->fetchCreated) {
                    $created = $this->order->import(
                        $this->accountIds,
                        $this->orderId,
                        null,
                        $this->status,
                        $this->lower,
                        50,
                        true,
                        "Created",
                        $this->upper
                    );
                }

                if ($this->fetchModified) {
                    $modified = $this->order->import(
                        $this->accountIds,
                        $this->orderId,
                        null,
                        $this->status,
                        $this->lower,
                        50,
                        true,
                        "Modified",
                        $this->upper
                    );
                }

                $this->sync();

                $this->unlock();
            }

            $tend = microtime(true);
            $execution = (($tend - $tstart)/60);
            $this->logger->info(
                'Amazon order import executed via cron.',
                [
                    'enabled' => $sync,
                    'created' => $created,
                    'modified' => $modified,
                    'lock' => $unlocked,
                    'time' => $execution
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        $this->result =
            "Amazon order import executed via cron. enabled: " .
            var_export($sync, true) . ", created: " . var_export($created, true) .
            ", modified: " . var_export($modified, true) . ", unlocked: " . var_export($unlocked, true);
    }

    private function sync()
    {
        $this->sync->setMedium($this->getMedium());
        $this->sync->setAccountIds($this->accountIds);
        $this->sync->execute();
    }

    /**
     * Set Import Medium
     * @param string $medium
     */
    public function setMedium($medium)
    {
        $this->medium = $medium;
    }

    /**
     * Get Import Medium
     * @return string
     */
    public function getMedium()
    {
        return $this->medium;
    }

    private function lock()
    {
        return $this->flagDir->touch(self::FLAG_FILENAME);
    }

    private function unlock()
    {
        if ($this->flagDir->isExist(self::FLAG_FILENAME)) {
            return $this->flagDir->delete(self::FLAG_FILENAME);
        }

        return true;
    }

    private function status()
    {
        $status = false;

        // In case of a died cron, it will re-consider the file after 30 mins of creation.
        if ($this->flagDir->isExist(self::FLAG_FILENAME)) {
            $stat = $this->flagDir->stat(self::FLAG_FILENAME);
            $now = time();
            $seconds = (30 * 60);
            $past = $now - $seconds;
            if (isset($stat['mtime']) && $stat['mtime'] < $past) {
                $status = true;
            }
        } else {
            $status = true;
        }

        return $status;
    }
}
