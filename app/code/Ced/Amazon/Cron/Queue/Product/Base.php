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

namespace Ced\Amazon\Cron\Queue\Product;

abstract class Base extends \Ced\Amazon\Cron\Queue\Processor\Base
{
    /**
     * @var \Amazon\Sdk\EnvelopeFactory
     */
    public $envelope;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface  */
    public $feed;

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

    /** @var \Ced\Amazon\Helper\Shipment  */
    public $shipment;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Amazon\Sdk\EnvelopeFactory $envelope,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Product $product,
        \Ced\Amazon\Helper\Product\Inventory $inventory,
        \Ced\Amazon\Helper\Product\Relationship $relation,
        \Ced\Amazon\Helper\Product\Image $image,
        \Ced\Amazon\Helper\Product\Price $price,
        \Ced\Amazon\Helper\Shipment $shipment
    ) {
        parent::__construct($dateTime, $serializer, $search, $filter, $queue, $cache, $config, $logger);
        $this->envelope = $envelope;
        $this->feed = $feed;

        $this->product = $product;
        $this->inventory = $inventory;
        $this->price = $price;
        $this->relation = $relation;
        $this->image = $image;
        $this->shipment = $shipment;
    }
}
