<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service\Order;

use Ced\Amazon\Api\Data\Order\Import\ParamsInterfaceFactory;
use Ced\Amazon\Api\Data\Order\Import\ResultInterface;
use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Model\ResourceModel\Order\CollectionFactory as AmazonOrderCollectionFactory;
use Ced\Amazon\Repository\Account as AccountRepository;
use Ced\Amazon\Service\Order as AccountOrderService;
use Ced\Amazon\Api\Data\Order\Import\ParamsInterface;

class Sync implements BulkActionProcessorInterface
{
    private $medium = "cron";

    /** @var AccountRepository */
    public $accountRepository;

    /** @var AmazonOrderCollectionFactory */
    public $amazonOrderCollectionFactory;

    /** @var ParamsInterfaceFactory  */
    public $paramsFactory;

    /** @var AccountOrderService  */
    public $amazonOrderService;

    /** @var Logger  */
    public $logger;

    public $accountIds = [];

    public $start;

    public $end;

    public function __construct(
        AccountRepository $accountRepository,
        AmazonOrderCollectionFactory $amazonOrderCollectionFactory,
        AccountOrderService $amazonOrderService,
        ParamsInterfaceFactory $paramsFactory,
        Logger $logger
    ) {
        $this->accountRepository = $accountRepository;
        $this->amazonOrderCollectionFactory = $amazonOrderCollectionFactory;
        $this->amazonOrderService = $amazonOrderService;
        $this->paramsFactory = $paramsFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $now = $this->getEndDate();
        $start = $this->getStartDate();

        $accountList = $this->accountRepository->getAvailableList($this->getAccountIds());

        foreach ($accountList->getItems() as $account) {
            $accountId = $account->getId();
            /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $collection */
            $collection = $this->amazonOrderCollectionFactory->create();
            $collection->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                [['null' => true], ['eq' => ""]]
            );
            $collection->addFieldToFilter(
                \Ced\Amazon\Model\Order::COLUMN_PO_DATE,
                ['from' => $start, 'to' => $now]
            );
            $collection->setPageSize(50);
            $size = $collection->getLastPageNumber();
            $size = $size >= 4 ? 4 : $size;
            // 200 order sync per 15 mins
            for ($page = 1; $page <= $size; $page++) {
                $collection->clear();
                $collection->setCurPage($page);
                $collection->load();
                $amazonOrderIdList = $collection->getColumnValues(\Ced\Amazon\Model\Order::COLUMN_PO_ID);
                /** @var \Ced\Amazon\Api\Data\Order\Import\ParamsInterface $params */
                $params = $this->paramsFactory->create();
                $params->setCreate(true)
                    ->setAmazonOrderId($amazonOrderIdList)
                    ->setAccountIds([$accountId]);

                $this->amazonOrderService->setMedium($this->getMedium());
                /** @var ResultInterface $result */
                $result = $this->amazonOrderService->import($params);
                $this->logger->info(
                    "Order sync completed via cron.",
                    [
                        "account_id" => $accountId,
                        'total' => $result->getOrderTotal(),
                        'imported' => $result->getOrderImportedTotal()
                    ]
                );
            }
        }
    }

    /**
     * Process the given ids
     * @param mixed $ids
     * @return boolean
     */
    public function process($ids)
    {
        try {
            $orderList = [];
            $accountId = 0;
            $amazonOrder = $this->amazonOrderCollectionFactory->create()
                ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_ID, ["in" => $ids]);
            foreach ($amazonOrder->getItems() as $amzOrder) {
                $orderList[$amzOrder->getAccountId()][] = $amzOrder->getAmazonOrderId();
            }

            $totalImported = 0;
            foreach ($orderList as $accountId => $amazonOrderIdList) {
                /** @var \Ced\Amazon\Api\Data\Order\Import\ParamsInterface $params */
                $params = $this->paramsFactory->create();
                $params->setCreate(true)
                    ->setAmazonOrderId($amazonOrderIdList)
                    ->setSyncMode(ParamsInterface::COLUMN_SYNC_MODE_NO_FETCH)
                    ->setAccountIds([$accountId]);

                /** @var ResultInterface $result */
                $result = $this->amazonOrderService->import($params);
                $totalImported += $result->getOrderImportedTotal();
            }

            $status = true;
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                " Error in Bulk Order sync.",
                [
                    "account_id" => $accountId,
                    'total' => isset($result) ? $result->getOrderTotal() : 0,
                    'imported' => isset($result) ? $result->getOrderImportedTotal() : 0,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }
        return $status;
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
            $this->start = date("Y-m-d H:i:s O", strtotime('-2 days', strtotime($this->getEndDate())));
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
}
