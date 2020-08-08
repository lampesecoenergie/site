<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product in category grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Ui\Form\Product;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productFactory;

    /** @var \Ced\Amazon\Model\Source\Profile  */
    public $profiles;

    /** @var \Ced\Amazon\Model\Source\Category  */
    public $categories;

    /** @var \Magento\Catalog\Model\CategoryFactory  */
    public $category;

    /**
     * Product constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\CategoryFactory $category
     * @param \Ced\Amazon\Model\Source\Profile $profiles
     * @param \Ced\Amazon\Model\Source\Category $categories
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Ced\Amazon\Model\Source\Profile $profiles,
        \Ced\Amazon\Model\Source\Category $categories,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->profiles = $profiles;
        $this->categories = $categories;
        $this->category = $category;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', ['_current' => true]);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amazon_profile_products');
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
        } elseif ($column->getId() == 'amazon_profile_id' && ($column->getFilter()->getValue() == '' ||
                $column->getFilter()->getValue() == null)) {
            $this->getCollection()->addFieldToFilter('amazon_profile_id', [
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
     * @return \Ced\Amazon\Model\Profile
     */
    public function getProfile()
    {
        return $this->coreRegistry->registry('amazon_profile');
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
        $collection = $this->productFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('amazon_profile_id')
            ->addAttributeToSelect('price')
            ->addCategoryIds();
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);

       /* $productIds = $this->_getSelectedProducts();
        if (!empty($productIds) && is_array($productIds)) {
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }*/

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
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
            'amazon_profile_id',
            [
                'header' => __('Profile'),
                'type' => 'options',
                'options' => $this->profiles->getOptionArray(),
                'index' => 'amazon_profile_id'

            ]
        );

        $this->addColumn(
            'category_ids',
            [
                'header' => __('Store Category'),
                'type' => 'options',
                'options' => $this->categories->getOptionArray(),
                'index' => 'category_ids',
                'renderer' => \Ced\Amazon\Block\Adminhtml\Profile\Ui\Form\Product\Renderer\Category::class,
                'filter_condition_callback' => [$this, 'filterCategory'],
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

        $this->addColumn(
            'actions',
            [
                'filter' => false,
                'header' => __('Actions'),
                'type' => 'text',
                'index' => 'actions',
                'renderer' => \Ced\Amazon\Block\Adminhtml\Profile\Ui\Form\Product\Renderer\Actions::class,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param $column
     * @return mixed
     */
    public function filterCategory($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $category = $this->category->create()->load($value);
        $collection->addCategoryFilter($category);
        return $collection;
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Reset Filter and Remove'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Search and Add'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }
}
