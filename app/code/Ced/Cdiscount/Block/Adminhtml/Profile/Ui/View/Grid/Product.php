<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 7/2/18
 * Time: 5:54 PM
 */

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View\Grid;

use Magento\Backend\Block\Widget\Grid\Column;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    public $profiles;

    public $categories;

    public $_objectManager;

    public $storeId;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Cdiscount\Model\Source\Profiles $profiles,
        \Ced\Cdiscount\Model\Source\Categories $categories,
        \Ced\Cdiscount\Helper\Config $config,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->profiles = $profiles;
        $this->categories = $categories;
        $this->_objectManager = $objectManager;
        $this->storeId = $config->getStore();
        parent::__construct($context, $backendHelper, $data);
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', ['_current' => true]);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('cdiscount_profile_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_profile') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } elseif($column->getId() == 'cdiscount_profile_id' && $column->getFilter()->getValue() == '0') {
            $this->getCollection()->addFieldToFilter('cdiscount_profile_id', [
                ['null' => 1],
                ['eq' => '']
            ]);
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getProfile()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }

    /**
     * @return array|null
     */
    public function getProfile()
    {
        return $this->_coreRegistry->registry('cdiscount_profile');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        //@TODO update get category dynamically
        if ($this->getProfile()->getId()) {
            $this->setDefaultFilter(['in_profile' => $this->getProfile()->getId()]);
        }
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'cdiscount_profile_id'
        )->addAttributeToSelect(
            'price'
        );
        if ($this->storeId > 0) {
            $collection->addStoreFilter($this->storeId);
        }
        $this->setCollection($collection);

        if ($this->getProfile()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        /*if (!$this->getProfile()->getProductsReadonly()) {
            $this->addColumn(
                'in_profile',
                [
                    'type' => 'checkbox',
                    'name' => 'in_profile',
                    'values' => $this->_getSelectedProducts(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }*/
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'cdiscount_profile_id',
            [
                'header' => __('Profile'),
                'type' => 'options',
                'options' => $this->profiles->getOptionArray(),
                'index' => 'cdiscount_profile_id'

            ]
        );

        $this->addColumn(
            'category_ids',
            [
                'header' => __('Store Category'),
                'type' => 'options',
                'options' => $this->categories->getOptionArray(),
                'index' => 'category_ids',
                'renderer' => 'Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View\Grid\Category',
                'filter_condition_callback' => array($this, 'filterCallback'),
            ]
        );

        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);

        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'filter' => false,
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price',
            ]
        );

        return parent::_prepareColumns();
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $_category = $this->_objectManager->create('\Magento\Catalog\Model\Category')->load($value);
        $collection->addCategoryFilter($_category);
        return $collection;
    }

}