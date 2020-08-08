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

namespace Ced\Amazon\Cron\Queue\Processor;

abstract class Base
{
    // When operationType is Delete.
    const PRODUCT_DELETE = '_POST_PRODUCT_DATA_DELETE_';
    const PROCESSOR_TYPE = "CRON";
    const LOGGING_TAG = 'Queue processing ';

    /** @var array */
    public $result = [];

    /** @var string  */
    public $cacheIdentifier = "processor_cron_status";

    /**
     * Initial Cron Status : Start with listing report
     */
    public $init = [
        \Amazon\Sdk\Api\Feed::PRODUCT => true,
        \Amazon\Sdk\Api\Feed::PRODUCT_PRICING => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP => false,
        \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT => false,
        self::PRODUCT_DELETE => false
    ];

    public $status = [
        \Amazon\Sdk\Api\Feed::PRODUCT => true,
        \Amazon\Sdk\Api\Feed::PRODUCT_PRICING => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE => false,
        \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP => false,
        \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT => false,
        self::PRODUCT_DELETE => false
    ];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * Json Parser
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /**
     * @var \Ced\Amazon\Model\Cache
     */
    public $cache;

    /**
     * @var \Ced\Amazon\Helper\Config
     */
    public $config;

    /**
     * @var \Ced\Amazon\Helper\Logger $logger,
     */
    public $logger;

    /**
     * @var \Ced\Amazon\Helper\Product
     */
    public $product;

    /**
     * @var \Ced\Amazon\Helper\Product\Inventory
     */
    public $inventory;

    /**
     * @var \Ced\Amazon\Helper\Product\Price
     */
    public $price;

    /** @var \Ced\Amazon\Helper\Product\Relationship  */
    public $relation;

    /** @var \Ced\Amazon\Helper\Product\Image  */
    public $image;

    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search  */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory  */
    public $filter;

    /** @var string, Default Action Type: Actually Performed */
    public $type = \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY;

    /** @var string, Current Action Type: Actually Staged */
    public $current;

    public $typeOverride;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->dateTime = $dateTime;
        $this->serializer = $serializer;
        $this->search = $search;
        $this->filter = $filter;

        $this->queue = $queue;

        $this->cache = $cache;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Stage Next Action to execute in status.
     * @return bool
     * @throws \Exception
     */
    public function stageNext()
    {
        // Checking type value. Exit on invalid value.
        $types = array_keys($this->init);
        if (!in_array($this->current, $types)) {
            $this->logger->error(
                "Queue processing: staging next failed. Invalid type set.",
                ['type' => $this->current,'processor' => self::PROCESSOR_TYPE]
            );
            return false;
        }

        if (!in_array($this->type, $types)) {
            $this->logger->error(
                "Queue processing: staging next failed. Invalid type set.",
                ['type' => $this->type,'processor' => self::PROCESSOR_TYPE]
            );
            return false;
        }

        // Setting cron status into the cache
        // $this->status[$this->current] = false;
        // $this->status[$this->type] = false;
        $this->status = array_fill_keys($types, false);
        $increment = 1;
        foreach ($types as $key => $type) {
            if ($type == $this->current) {
                if (!isset($types[$key + $increment])) {
                    // In case of last index, set to first.
                    $next = $types[0];
                } else {
                    // Increasing the next by 1 index, if it is currently being processed
                    if ($types[$key + $increment] == $this->type) {
                        if (!isset($types[$key + $increment + 1])) {
                            // In case of last index, set to first.
                            $next = $types[0];
                        } else {
                            $next = $types[$key + $increment + 1];
                        }
                    } else {
                        $next = $types[$key + $increment];
                    }
                }

                $this->status[$next] = true;
                break;
            }
        }

        $this->logger->info(
            self::LOGGING_TAG . "staging next completed.",
            ['status' => $this->status,'processor' => self::PROCESSOR_TYPE]
        );
        $this->cache->setValue($this->cacheIdentifier, $this->status);
        return true;
    }

    /**
     * Get Results
     * @param boolean $json
     * @return string|array
     */
    public function getResult($json = true)
    {
        if ($json) {
            return json_encode($this->result);
        }

        return $this->result;
    }

    /**
     * Set Results
     * @param array $result
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Add Value to Result
     * @param string $key
     * @param string|array|null $value
     * @return void
     */
    public function addResult($key, $value = null)
    {
        if (!empty($key)) {
            $this->result[$key] = $value;
        }
    }

    /**
     * Set Job Type
     * @param $type
     * @return void
     */
    public function setType($type)
    {
        if (isset($this->status[$type])) {
            $this->type = $type;
        }
    }

    /**
     * Get Job Type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Job Type Override
     * @param $type
     * @return void
     */
    public function setTypeOverride($type)
    {
        if (isset($this->status[$type])) {
            $this->typeOverride = $type;
        }
    }

    /**
     * Get Job Type Override
     * @return string
     */
    public function getTypeOverride()
    {
        return $this->typeOverride;
    }
}
