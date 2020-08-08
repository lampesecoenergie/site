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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;

/**
 * Class EbayMultiAccountProduct
 * @package Ced\EbayMultiAccount\Ui\DataProvider\Product
 */
class EbayMultiAccountProduct extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var
     */
    public $collection;

    public $profileIdAccAttr;

    /**
     * @var array
     */
    public $addFieldStrategies;

    /**
     * @var array
     */
    public $addFilterStrategies;
    /**
     * @var FilterBuilder
     */
    public $filterBuilder;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    protected $dataHelper;

    /**
     * EbayMultiAccountProduct constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        try {
            parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
            $this->dataHelper = $dataHelper;
            $accountId = $this->dataHelper->getAccountSession();
            $this->multiAccountHelper = $multiAccountHelper;
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->_coreRegistry = $registry;
            $accountId = 0;
            if ($account) {
                $accountId = $account->getId();
            }
            $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
            $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
            $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
            $this->profileIdAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($accountId);
            $this->filterBuilder = $filterBuilder;
            $this->objectManager = $objectManager;
            $pids = $this->objectManager->create('Ced\EbayMultiAccount\Model\Profile')->getCollection()->addFieldToFilter('profile_status', 1)->getColumnValues('id');
            $dumy_collection = $collectionFactory->create();
            $dumy_collection->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null);

            $this->collection = $collectionFactory->create();
            $this->collection->addFieldToSelect(array($listingErrorAccAttr, $itemIdAccAttr, $this->profileIdAccAttr, $prodStatusAccAttr));
            $this->collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id = entity_id', '{{table}}.stock_id=1', null);
            $this->addField($prodStatusAccAttr);
            $this->addField($listingErrorAccAttr);
            $this->addField($itemIdAccAttr);
            $this->collection->joinAttribute('ebaymultiaccount_item_id', "catalog_product/$itemIdAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('ebaymultiaccount_listing_error', "catalog_product/$listingErrorAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('ebaymultiaccount_product_status', "catalog_product/$prodStatusAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('ebaymultiaccount_profile_id', "catalog_product/$this->profileIdAccAttr", 'entity_id', null, 'left');
            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('notnull')
                ->setValue('true')
                ->create());
            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('in')
                ->setValue($pids)
                ->create());
            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('neq')
                ->setValue(0)
                ->create());
            $this->addFilter($this->filterBuilder->setField('type_id')->setConditionType('in')
                ->setValue(['simple', 'configurable'])
                ->create());
            $this->addFilter($this->filterBuilder->setField('visibility')->setConditionType('neq')
                ->setValue(1)
                ->create());
            $this->addFieldStrategies = $addFieldStrategies;
            $this->addFilterStrategies = $addFilterStrategies;
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        if ($this->profileIdAccAttr == '') {
            return [ 'totalRecords' => 0, 'items' => []];
        } else {
            return [
                'totalRecords' => $this->getCollection()->getSize(),
                'items' => array_values($items),
            ];
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @param array|string $field
     * @param null $alias
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}
