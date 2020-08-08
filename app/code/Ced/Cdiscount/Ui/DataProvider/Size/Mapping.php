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
 * @package     Ced_Lazada
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Ui\DataProvider\Size;


class Mapping extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public $collection;

    public $loadedData;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;


    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes\CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->loadedData = $this->collection->load()->getData();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->getData();
        if (empty($items)) {
            $items = $this->loadedData;
        }
        $preparedData = [];
        foreach ($items as  &$item) {
            $attrs = json_decode($item['attribute_mappings'], true);
            foreach ($attrs as $key =>  $attr) {
                $preparedData[$item['id']]['size'][] = [
                    'id' => $item['id'],
                    'record_id' => $key,
                    'position' => $key+1,
                    'magento_size' => $attr['magento_size_id'],
                    'cdiscount_size' => [$attr['cdiscount_size_id']]
                ];
            }
        }

        return $preparedData;
    }
}