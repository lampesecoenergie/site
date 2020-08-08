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

namespace Ced\Amazon\Ui\DataProvider\Account\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $url;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $collectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
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

        /** @var array $items */
        $items = $this->getCollection();
        $data = [];

        /** @var \Ced\Amazon\Model\Account $item */
        foreach ($items as &$item) {
            $active = $item->getData(\Ced\Amazon\Model\Account::COLUMN_ACTIVE);
            $active = $active != "1" ? 0 : $active;
            $item->setData(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, $active);

            $item->setData("id_field_name", \Ced\Amazon\Model\Account::ID_FIELD_NAME);

            $multistore = $item->getData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE);
            $multistore = $multistore != "1" ? 0 : $multistore;
            $item->setData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE, $multistore);

            $stores = $item->getData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES);
            $stores = !empty($stores) ? json_decode($stores, true) : [];
            $item->setData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES, $stores);

            $data[$item->getId()] = $item->getData();
        }

        return $data;
    }
}
