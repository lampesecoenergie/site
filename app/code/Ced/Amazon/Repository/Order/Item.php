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

namespace Ced\Amazon\Repository\Order;

use Ced\Amazon\Model\ResourceModel\Order\Item\CollectionFactory as AmazonOrderItemCollectionFactory;
use Ced\Amazon\Model\Order\ItemFactory as AmazonOrderItemFactory;
use Ced\Amazon\Api\Order\ItemRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class Item implements ItemRepositoryInterface
{
    public $collectionFactory;

    public $modelFactory;

    public function __construct(
        AmazonOrderItemCollectionFactory $collectionFactory,
        AmazonOrderItemFactory $modelFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
    }

    /**
     * Get Amazon Order Item
     * @param $orderItemId
     * @return \Ced\Amazon\Api\Data\Order\ItemInterface
     * @throws LocalizedException
     */
    public function getByMagentoOrderItemId($orderItemId)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(\Ced\Amazon\Model\Order\Item::COLUMN_MAGENTO_ORDER_ITEM_ID, ['eq' => $orderItemId])
            ->setPageSize(1)
            ->setCurPage(1);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        } else {
            throw new LocalizedException(__("No Item Exists"));
        }
    }
}
