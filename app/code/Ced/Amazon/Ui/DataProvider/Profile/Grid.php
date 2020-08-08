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

namespace Ced\Amazon\Ui\DataProvider\Profile;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Grid
 */
class Grid extends AbstractDataProvider
{
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

    /** @var \Ced\Amazon\Model\Profile\Product  */
    public $product;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        \Ced\Amazon\Model\Profile\Product $product,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->product = $product;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $items = $this->getCollection()
            ->addFieldToSelect([
                \Ced\Amazon\Model\Profile::COLUMN_ID,
                \Ced\Amazon\Model\Profile::COLUMN_NAME,
                \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID,
                \Ced\Amazon\Model\Profile::COLUMN_STORE_ID,
                \Ced\Amazon\Model\Profile::COLUMN_STATUS,
                \Ced\Amazon\Model\Profile::COLUMN_CATEGORY,
                \Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY,
                \Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE,
            ])
            ->getData();

        /** @var \Ced\Amazon\Model\Profile $item */
        foreach ($items as &$item) {
            $item['id_field_name'] = 'id';
            $item['total_products'] = (string)$this->product->getSize(
                $item[\Ced\Amazon\Model\Profile::COLUMN_ID],
                $item[\Ced\Amazon\Model\Profile::COLUMN_STORE_ID]
            );
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
