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

use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Service\CustomerServiceInterface;
use Ced\Amazon\Helper\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class Customer implements CustomerServiceInterface
{
    use \Ced\Amazon\Service\Common;

    /** @var CustomerRepositoryInterface  */
    public $customerRepository;

    /** @var CustomerInterfaceFactory  */
    public $customerFactory;

    /** @var ConfigServiceInterface  */
    public $config;

    /** @var Logger  */
    public $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        ConfigServiceInterface $config,
        Logger $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get or Create Customer
     * @param \Amazon\Sdk\Api\Order $order
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param null|string $groupId
     * @return null|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function get($order, $store, $groupId = null)
    {
        $customer = null;

        try {
            $email = $this->config->getDefaultCustomer();
            if (!empty($email)) {
                /**
                 * case 1: Use default customer.
                 */
                try {
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerRepository->get($email, $store->getWebsiteId());
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __("Default Customer does not exists. Customer Id: #%s.", $email)
                    );
                }
            } else {
                /** Case 2: Use Customer from Order.*/
                $email = $this->email($order);
                try {
                    /** Case 2.1 Get Customer if already exists. */
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerRepository->get($email, $store->getWebsiteId());
                } catch (\Exception $e) {
                    // Case 2.1 : Create customer if does not exists.
                    if(empty($order->getBuyerName())) {
                        $name = ['Amazon Customer', 'FBA'];
                    } else {
                        $name = explode(' ', $order->getBuyerName(), 2);
                    }
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerFactory->create();
                    $customer->setStoreId($store->getId());
                    $customer->setWebsiteId($store->getWebsiteId());
                    $customer->setEmail($email);
                    $customer->setFirstname(isset($name[0]) ? $name[0] : '.');
                    $customer->setLastname(isset($name[1]) ? $name[1] : '.');
                    if (isset($groupId)) {
                        $customer->setGroupId($groupId);
                    }
                    $customer = $this->customerRepository->save($customer);
                }
            }
        } catch (\Exception $e) {
            $customer = null;
            $this->logger->log(
                'ERROR',
                'Customer create failed. Order Id: #' . $order->getAmazonOrderId(),
                [
                    'message' => $e->getMessage(),
                    'order_id' => $order->getAmazonOrderId()
                ]
            );
        }

        return $customer;
    }
}
