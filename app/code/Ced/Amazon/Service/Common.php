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

namespace Ced\Amazon\Service;

use Magento\Framework\Exception\LocalizedException;

trait Common
{
    private $medium = "cron";

    /**
     * Get Email for Order
     * @param \Amazon\Sdk\Api\Order $order
     * @return string
     */
    private function email(\Amazon\Sdk\Api\Order $order)
    {
        $email = $order->getBuyerEmail();
        if (isset($email) && !empty($email)) {
            return $email;
        } else {
            // BuyerEmail is not returned for Fulfillment by Amazon gift orders.
            return $order->getAmazonOrderId() . "@amazon.com";
        }
    }

    /**
     * Get value from an array
     * @param string|int $index
     * @param array $haystack
     * @param string|null $default
     * @return null|string
     */
    public function getValue($index, $haystack = [], $default = null)
    {
        $value = $default;
        if (isset($index, $haystack[$index]) && !empty($haystack[$index])) {
            $value = $haystack[$index];
        }
        return $value;
    }

    /**
     * round off
     * 20.02 => 20.00
     * 20.499 => 20.50
     * 20.994 => 21.00
     * @param $value
     * @return float
     */
    public function round($value)
    {
        $rounded = round($value * 10);
        return number_format($rounded / 10, 2);
    }

    /**
     * Create Magento Increment Id by Rules
     * @param string $incrementId
     * @param \Amazon\Sdk\Api\Order $apiOrder
     * @return  string, Ex: AMZ-US111-111111-111111
     */
    private function generateIncrementId($incrementId, $apiOrder)
    {
        /** @var array $rules */
        $rules = $this->config->getIncrementIdRules();

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_AMAZON_ORDER_ID, $rules)) {
            $incrementId = $apiOrder->getAmazonOrderId();
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_MARKETPLACE_CODE, $rules)) {
            $marketplaceId = $apiOrder->getMarketplaceId();
            $code = \Amazon\Sdk\Marketplace::getCodeByMarketplaceId($marketplaceId);
            if (!empty($code)) {
                $incrementId = $code . $incrementId;
            }
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_FULFILLMENT_CHANNEL, $rules)) {
            $channelPrefix = $apiOrder->getFulfillmentChannel();
            if (!empty($channelPrefix)) {
                $incrementId = $channelPrefix . '-' . $incrementId;
            }
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_PREFIX, $rules)) {
            $prefix = $this->config->getOrderIdPrefix();
            if (!empty($prefix)) {
                $incrementId = $prefix . $incrementId;
            }
        }

        return $incrementId;
    }

    /**
     * Generate Invoice
     * @param \Magento\Sales\Model\Order $order
     * @throws LocalizedException
     * @deprecated : Payment method auto invoice
     */
    public function invoice($order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $objectManager->create(\Magento\Sales\Model\Service\InvoiceService::class)
            ->prepareInvoice($order);
        //$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->pay()->save();
        $transactionSave = $objectManager->create(\Magento\Framework\DB\Transaction::class)
            ->addObject($invoice)->addObject($invoice->getOrder());
        $transactionSave->save();

        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        $order->addStatusToHistory(
            $order->getStatus(),
            __('\'Order invoiced #%1 automatically after import.', $invoice->getId())
        );
        $order->save();
    }

    /**
     * Add Message to Order
     * @param string $message
     * @param \Magento\Sales\Model\Order $order
     */
    public function addMessage($message, $order)
    {
        $order->addStatusToHistory($order->getStatus(), __($message, $this->getMedium()));
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

    /**
     * Find/Load Magento Order By IncrementId
     * @param string $incrementId
     * @return \Magento\Framework\DataObject|null
     */
    private function findByIncrementId($incrementId)
    {
        $result = null;
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->magentoOrderCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', ['eq' => $incrementId]);
        $collection->addFieldToSelect(['entity_id', 'increment_id']);
        $collection->setCurPage(1);
        $collection->setPageSize(1);
        if ($collection->getSize() > 0) {
            $result = $collection->getFirstItem();
        }

        return $result;
    }
}
