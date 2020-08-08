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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Create
{
    const FLAG_FILENAME = '.amazon.order.create.lock';

    /**
     * Maintenance flag dir
     */
    const FLAG_DIR = DirectoryList::VAR_DIR;

    /**
     * Path to store files
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    public $flagDir;

    /** @var \Ced\Amazon\Helper\Logger  */
    public $logger;

    /** @var \Ced\Amazon\Service\Config  */
    public $config;

    /** @var \Ced\Amazon\Service\Order  */
    public $order;

    /** @var \Ced\Amazon\Api\ReportRepositoryInterface  */
    public $reportRepository;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface  */
    public $accountRepository;

    /** @var \Ced\Amazon\Api\Data\Order\Import\ParamsInterfaceFactory */
    public $paramsFactory;

    public $sleep = 120;

    public $start;

    public $end;

    public $accountIds = [];

    public function __construct(
        Filesystem $filesystem,
        \Ced\Amazon\Api\ReportRepositoryInterface $reportRepository,
        \Ced\Amazon\Api\AccountRepositoryInterface $accountRepository,
        \Ced\Amazon\Api\Data\Order\Import\ParamsInterfaceFactory $paramsFactory,
        \Ced\Amazon\Service\Order $order,
        \Ced\Amazon\Service\Config $config,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->flagDir = $filesystem->getDirectoryWrite(self::FLAG_DIR);

        $this->reportRepository = $reportRepository;
        $this->accountRepository = $accountRepository;
        $this->paramsFactory = $paramsFactory;
        $this->order = $order;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        $unlocked = false;
        $sync = false;
        try {
            $sync = (boolean)$this->config->getOrderImport();
            $unlocked = $this->status();
            if ($sync && $unlocked) {
                $this->lock();

                // Get report and create orders in Amazon Table
                $this->create();

                $this->unlock();
            }

            $this->logger->info(
                'Amazon order create executed via cron.',
                ['enabled' => $sync, 'lock' => $unlocked]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        $this->result =
            "Amazon order create executed via cron. enabled: " .
            var_export($sync, true) . ", unlocked: " . var_export($unlocked, true);
    }

    private function create()
    {

        $now = $this->getEndDate();
        $start = $this->getStartDate();

        $accountList = $this->accountRepository->getAvailableList($this->getAccountIds());
        /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
        foreach ($accountList->getItems() as $account) {
            $accountId = $account->getId();
            $this->logger->debug("Order Create Period : " . $start . "---" . $now . " account #" . $accountId);

            $path = $this->get($account, $start, $now);
            /** @var \Ced\Amazon\Api\Data\Order\Import\ParamsInterface $params */
            $params = $this->paramsFactory->create();
            $params->setPath($path)
                ->setMode(\Ced\Amazon\Api\Data\Order\Import\ParamsInterface::IMPORT_MODE_REPORT)
                ->setCreate(false)
                ->setAccountIds([$accountId]);
            $result = $this->order->import($params);
            $created = $result->getOrderImportedTotal();
            $this->logger->debug("Orders imported {$created}", ['order_ids' => $result->getIds()]);
        }
    }

    /**
     * Get Report Path
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @param string $startDate
     * @param string $endDate
     * @return string|null
     */
    private function get($account, $startDate, $endDate)
    {
        $path = null;
        $requestId = $this->request($account, $startDate, $endDate);
        $this->logger->debug("Report Requested : Request Id " . $requestId, ['path' => __METHOD__]);
        while (true && isset($requestId)) {
            sleep($this->sleep);

            $status = $this->reportRepository->get($requestId);
            if ($status == \Ced\Amazon\Model\Source\Feed\Status::CANCELLED) {
                $requestId = $this->request($account, $startDate, $endDate);
                continue;
            }

            if (in_array(
                $status,
                [
                    \Ced\Amazon\Model\Source\Feed\Status::DONE,
                    \Ced\Amazon\Model\Source\Feed\Status::DONE_NO_DATA
                ]
            )) {
                /** @var \Ced\Amazon\Api\Data\ReportInterface $report */
                $request = $this->reportRepository->getByRequestId($requestId);
                $path = $request->getReportFile();
                $this->logger->debug("Report File : " . var_export($path, true), ['path' => __METHOD__]);
                if (!empty($path)) {
                    break;
                }
            }

            $this->logger->debug("Waiting : Report Get. Status: " . $status, ['path' => __METHOD__]);
        }

        return $path;
    }

    /**
     * Request a report
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @param string $startDate
     * @param string $endDate
     * @return bool|int|null
     */
    private function request($account, $startDate, $endDate)
    {
        $specifics = [
            'type' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_FLAT_ORDER_DATA,
            'account_id' => $account->getId(),
            'ids' => ['*'],
            'marketplace' => '',
            'profile_id' => 0,
            'store_id' => $account->getStoreId(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        $requestId = null;
        while (true) {
            $requestId = $this->reportRepository->request($specifics);
            if (!empty($requestId)) {
                break;
            }
            $this->logger->debug("Waiting : Report Request", ["path" => __METHOD__]);
            sleep($this->sleep);
        }

        return $requestId;
    }

    public function getAccountIds()
    {
        return $this->accountIds;
    }

    public function setAccountIds($ids = [])
    {
        if (is_array($ids)) {
            $this->accountIds = $ids;
        }
    }

    public function setStartDate($date)
    {
        if (!empty($date)) {
            $this->start = date("Y-m-d H:i:s O", strtotime($date));
        }
    }

    public function setEndDate($date)
    {
        if (!empty($date)) {
            $this->end = date("Y-m-d H:i:s O", strtotime($date));
        }
    }

    public function getStartDate()
    {
        if (!isset($this->start)) {
            $this->start = date("Y-m-d H:i:s O", strtotime('-1 days', strtotime($this->getEndDate())));
        }
        return $this->start;
    }

    public function getEndDate()
    {
        if (!isset($this->end)) {
            $this->end = date("Y-m-d H:i:s O");
        }

        return $this->end;
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
