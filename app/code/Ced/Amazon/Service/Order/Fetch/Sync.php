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

namespace Ced\Amazon\Service\Order\Fetch;

use Ced\Amazon\Api\Data\Order\Import\ParamsInterface;
use Ced\Amazon\Api\Data\Order\Import\ResultInterface;

class Sync extends \Ced\Amazon\Service\Order\Sync
{
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
                    ->setSyncMode(ParamsInterface::COLUMN_SYNC_MODE_FETCH)
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
}
