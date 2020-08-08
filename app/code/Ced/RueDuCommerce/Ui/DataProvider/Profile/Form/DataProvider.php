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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Ui\DataProvider\Profile\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class ProductDataProvider
 */
class DataProvider extends AbstractDataProvider
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
     * @param \Ced\RueDuCommerce\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\RueDuCommerce\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
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
        $items = $this->getCollection()->getData();

        $data = [];

        foreach ($items as $item) {
            $categoryAndAttributes = [
                'profile_category' => $item['profile_category']
            ];

            $products = [
                'magento_category' => json_decode($item['magento_category'], true)
            ];

            $info = [
                'id' => $item['id'],
                'profile_code' => $item['profile_code'],
                'profile_status' => $item['profile_status'],
                'profile_name' => $item['profile_name'],
            ];

            /*$offers = [
                'profile_logistic_class' => $item['profile_logistic_class'],
                'profile_tax_au' => $item['profile_tax_au'],
                'profile_offer_state' => $item['profile_offer_state'],
                'profile_rueducommerce_club_eligible' => $item['profile_rueducommerce_club_eligible'],
            ];*/
            $data[$item['id']] = [
                'general_information' => $info,
                /*'offer_information' => $offers,*/
                'profile_mappings' => $categoryAndAttributes,
                'store_categories' => $products
            ];
        }

        return $data;
    }

}
