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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\System;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\Amazon\Model\ResourceModel\Account\CollectionFactory;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Model\Cache;
use Amazon\Sdk\Api\Service\StatusFactory;

class Sync extends Action
{
    const CACHE_IDENTIFIER = "ced_amazon_dashboard";

    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /** @var ModuleListInterface  */
    public $moduleList;

    /** @var Logger  */
    public $logger;

    /** @var Cache  */
    public $cache;

    /** @var CollectionFactory  */
    public $accountCollectionFactory;

    /** @var StatusFactory  */
    public $statusFactory;

    /**
     * Sync constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ModuleListInterface $moduleList
     * @param Logger $logger
     * @param Cache $cache
     * @param CollectionFactory $accountCollectionFactory
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ModuleListInterface $moduleList,
        Logger $logger,
        Cache $cache,
        CollectionFactory $accountCollectionFactory,
        StatusFactory $statusFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->statusFactory = $statusFactory;
    }

    public function execute()
    {
        $refresh = $this->getRequest()->getParam("refresh", false);
        $cacheValue = $this->cache->getValue(self::CACHE_IDENTIFIER);
        if (!empty($cacheValue) && is_array($cacheValue) && empty((boolean)$refresh)) {
            $data = $cacheValue;
        } else {
            $integrator = $this->moduleList->getOne("Ced_Integrator");
            $amazon = $this->moduleList->getOne("Ced_Amazon");
            $data = [
                "accounts" => [],
                "system" => [
                    [
                        "key" => "PHP",
                        "value" => phpversion(),
                    ],
                    [
                        "key" => "Memory",
                        "value" => $this->getMemory(),
                    ],
                    [
                        "key" => "Integrator",
                        "value" => isset($integrator['setup_version']) ? $integrator['setup_version'] : '-',
                    ],
                    [
                        "key" => "Amazon",
                        "value" => isset($amazon['setup_version']) ? $amazon['setup_version'] : '-',
                    ],
                ],
            ];
            $collection = $this->accountCollectionFactory->create();
            /** @var \Ced\Amazon\Model\Account $account */
            foreach ($collection->getItems() as $account) {
                $accountData = [
                    "id" => $account->getId(),
                    "name" => $account->getName(),
                    "api" => [

                    ]
                ];
                $config = $account->getConfig();
                /** @var \Amazon\Sdk\Api\Service\Status $status */
                $status = $this->statusFactory->create([
                    "service" => null,
                    "config" => $config,
                    "logger" => $this->logger,
                ]);

                foreach (\Amazon\Sdk\Api\Service\Status::SERVICE_LIST as $service) {
                    $status->setService($service);
                    $status->fetchServiceStatus();
                    $api = [
                        "service" => $service,
                        "status" => $status->getStatus(),
                        "timestamp" => $status->getTimestamp(),
                        "ready" => $status->isReady(),
                    ];

                    $accountData["api"][] = $api;
                }

                $data["accounts"][] = $accountData;
            }

            $this->cache->setValue(self::CACHE_IDENTIFIER, $data);
        }

        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultJsonFactory->create();
        $response->setData($data);

        return $response;
    }

    private function getMemory()
    {
        $memory = "-";
        try {
            $memory = ini_get('memory_limit');
        } catch (\Exception $e) {
            // pass
        }

        return $memory;
    }
}
