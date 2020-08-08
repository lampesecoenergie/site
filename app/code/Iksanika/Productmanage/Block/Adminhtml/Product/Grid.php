<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml customer grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Iksanika\Productmanage\Block\Adminhtml\Product;

use Magento\Store\Model\Store;
use Magento\Search\Model\QueryFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;
    
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Iksanika\Productmanage\Model\Product\Attribute\Source\Status
     */
    protected $_availability;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    protected $_template = 'Iksanika_Productmanage::widget/grid/extended.phtml';
    
    
    
    public static $columnSettings = array();
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param QueryFactory $queryFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
//        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Iksanika\Productmanage\Model\Product\Attribute\Source\Status $availability,
        \Iksanika\Productmanage\Helper\Category $helperCategory,
        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Tax\Model\TaxClass\Source\Product $taxClassSourceProduct,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $eavEntityOptCollection,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\CatalogSearch\Helper\Data $catalogSearch,
        QueryFactory $queryFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,         
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_availability = $availability;
        $this->moduleManager = $moduleManager;
        $this->_taxClassSourceProduct = $taxClassSourceProduct;
        $this->_eavEntityOptCollection = $eavEntityOptCollection;
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_helperCategory = $helperCategory;
        $this->_helper->setScopeConfig($this->_scopeConfig);
        $this->_productModel = $productModel;
        $this->_catalogSearch = $catalogSearch;
        $this->queryFactory = $queryFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        
        $this->isAllowed = array(
            'related' => (int)$this->_scopeConfig->getValue('iksanika_productmanage/productrelator/enablerelated') === 1,
            'cross_sell' => (int)$this->_scopeConfig->getValue('iksanika_productmanage/productrelator/enablecrosssell') === 1,
            'up_sell' => (int)$this->_scopeConfig->getValue('iksanika_productmanage/productrelator/enableupsell') === 1
        );
        $this->setId('productGrid');
        
        $this->prepareDefaults();
        
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
//        $this->setVarNameFilter('product_filter');
        
//        $this->prepareColumnSettings();
//        $this->setTemplate('iksanika/productupdater/catalog/product/grid.phtml');
//        $this->setMassactionBlockName('productupdater/widget_grid_massaction');
    }
    
    private function prepareDefaults() 
    {
        $this->setDefaultLimit($this->_scopeConfig->getValue('iksanika_productmanage/columns/limit'));
        $this->setDefaultPage($this->_scopeConfig->getValue('iksanika_productmanage/columns/page'));
        $this->setDefaultSort($this->_scopeConfig->getValue('iksanika_productmanage/columns/sort'));
        $this->setDefaultDir($this->_scopeConfig->getValue('iksanika_productmanage/columns/dir'));
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('save_config_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'label'     => __('Save Config'),
                    'onclick'   => $this->getJsObjectName().'.doSaveConfig(\''.($this->getUrl('productmanage/*/saveConfig', array('_current' => true))).'\')',
                    'class'     => 'primary btnSaveConfig'
                ]
            )
        );
        
        return parent::_prepareLayout();
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
//                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
//                    'class' => 'action-reset action-tertiary iks-btn iks-search-reset'
                    'class' => 'iks-btn iks-search-reset'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
//                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
//                    'class' => 'action-secondary iks-btn iks-search',
                    'class' => 'iks-btn iks-search',
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }
    
    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }
    
    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset(
                $data[$columnId]
            ) && (is_array(
                $data[$columnId]
            ) && !empty($data[$columnId]) || strlen(
                $data[$columnId]
            ) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                if($columnId != 'category_ids' && $columnId != 'category_ids' && $columnId != 'related_ids' && $columnId != 'cross_sell_ids' &&
                    $columnId != 'associated_configurable_ids' && $columnId != 'associated_groupped_ids')
                    $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }
    
    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $columnIndex = ($columnIndex == 'category_ids') ? 'cat_ids' : $columnIndex;
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }
    
    public function getQuery() 
    {
        return urldecode($this->getParam('q'));
    }
    
    public function getSaveConfigButtonHtml()
    {
        return $this->getChildHtml('save_config_button');
    }
    
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
//        $collection = $this->getCollection();
//        $collection = !$collection ? $this->_productModel->getCollection() : $collection;
        
        $collection = $this->_productFactory->create()->getCollection();
        
        if($queryString = $this->getQuery())
        {
            $query = $this->queryFactory->get();
            $query->setStoreId($this->_helper->getStoreId());
            $collection = $query->getSearchCollection();
            $collection->addSearchFilter($this->getQuery());
            $collection->addBackendSearchFilter($this->getQuery());
            $collection->addAttributeToSelect('*');
        }
        
        $store = $this->_getStore();
//        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
        $collection->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        )->setStore(
            $store
        );
        
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
            $collection->joinField(
                'is_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        
        
        $collection->joinField(
                'cat_ids',
                'catalog_category_product',
                'category_id',
                'product_id=entity_id',
                null,
                'left')
            ->joinField(
                'category',
                'catalog_category_product',
                'category_id',
                'product_id=entity_id',
                null,
                'left')
            ->joinField(
                'related_ids',
                'catalog_product_link',
                'linked_product_id',
                'product_id=entity_id',
                '{{table}}.link_type_id='.\Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED, // 1- relation, 4 - up_sell, 5 - cross_sell
                'left')
            ->joinField(
                'cross_sell_ids',
                'catalog_product_link',
                'linked_product_id',
                'product_id=entity_id',
                '{{table}}.link_type_id='.\Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL, // 1- relation, 4 - up_sell, 5 - cross_sell
                'left')
            ->joinField(
                'up_sell_ids',
                'catalog_product_link',
                'linked_product_id',
                'product_id=entity_id',
                '{{table}}.link_type_id='.\Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL, // 1- relation, 4 - up_sell, 5 - cross_sell
                'left')
                /*
            ->joinField(
                'associated_groupped_ids',
                'catalog/product_link',
                'linked_product_id',
                'product_id=entity_id',
                '{{table}}.link_type_id='.Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED, // 1- relation, 4 - up_sell, 5 - cross_sell
                'left')
                 */
            ->joinField(
                'associated_configurable_ids',
                'catalog_product_super_link',
                'product_id',
                'parent_id=entity_id',
                null, // 1- relation, 4 - up_sell, 5 - cross_sell
                'left')
            ->joinField(
                'tier_price',
                'catalog_product_entity_tier_price', //Mage::getConfig()->getTablePrefix().
                'value', 
                'entity_id=entity_id',
                null,//'{{table}}.website_id='.$store->getId(),
                'left');                
        
        $collection->groupByAttribute('entity_id');
        
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        
        
        
        // EG: Select all needed columns.
        //id,name,type,attribute_set,sku,price,qty,visibility,status,websites,image
//        foreach(self::$columnSettings as $col => $true) 
        foreach($this->_helper->getColumnSettings() as $col => $true) 
        {
            if($col == 'category_ids')
            {
                //$filter = $this->getParam('filter');
//                echo $this->getVarNameFilter().'~';
                $filter = $this->getParam($this->getVarNameFilter());
                if($filter)
                {
                    $filter_data = $this->_backendHelper->prepareFilterString($filter);
                    if(isset($filter_data['category_ids']))
                    {
                        if(trim($filter_data['category_ids'])=='')
                            continue;
                        $categoryIds = explode(',', $filter_data['category_ids']);
                        $catIdsArray = array();
                        foreach($categoryIds as $categoryId)
                        {
                            //$collection->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId));
                            $catIdsArray[] = $categoryId;
                        }
                        $collection->addAttributeToFilter('cat_ids', array( 'in' => $catIdsArray));                        
                        //$collection->printLogQuery(true);
                    }
                }
            }
            if($col == 'related_ids' || $col == 'cross_sell_ids' || $col == 'up_sell_ids' || 
                    $col == 'associated_groupped_ids' || $col == 'associated_configurable_ids')
            { 
                $filter = $this->getParam($this->getVarNameFilter());
                if($filter)
                {
                    $filter_data = $this->_backendHelper->prepareFilterString($filter);
                    if(isset($filter_data[$col]))
                    {
                        if(trim($filter_data[$col])=='')
                            continue;
                        $relatedIds = explode(',', $filter_data[$col]);
                        $relatedIdsArray = array();
                        foreach($relatedIds as $relatedId)
                        {
                            //$collection->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId));
                            $relatedIdsArray[] = intval($relatedId);
                        }
                        $collection->addAttributeToFilter($col, array( 'in' => $relatedIdsArray));                        
                    }
                }
            }
            /*
            if($col == 'sku')
            {
                $filter = $this->getParam($this->getVarNameFilter());
                if($filter)
                {
                    $filter_data = Mage::helper('adminhtml')->prepareFilterString($filter);
                    if(isset($filter_data['sku']))
                    {
                        if(trim($filter_data['sku'])=='')
                            continue;
                        $skuIds = explode(',', $filter_data['sku']);
                        $skuIdsArray = array();
                        foreach($skuIds as $skuId)
                            $skuIdsArray[] = $skuId;
                        $collection->addAttributeToFilter('sku', array( 'inset' => $skuIdsArray));                        
                    }
                }
            }
           */
            if($col == 'qty' || $col == 'websites' || $col=='id' || $col=='category_ids' || $col=='related_ids' || 
                    $col=='cross_sell_ids' || $col=='up_sell_ids' || $col=='associated_groupped_ids' || 
                    $col=='associated_configurable_ids' || $col=='group_price') 
                continue;
            else
                $collection->addAttributeToSelect($col);
        }
        
        $collection->addWebsiteNamesToResult();
        $this->setCollection($collection);
        //die(get_class($this->getCollection()));
        //$collection->printLogQuery(true);
        parent::_prepareCollection();
        //$this->getCollection()->addWebsiteNamesToResult();
        //$collection->printLogQuery(true);
        return $this;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }
    
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        if($this->_helper->colIsVisible('entity_id'))
        {
            $this->addColumn(
                'entity_id',
                [
                    'header' => __('ID'),
                    'type' => 'number',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
            );
        }
        
        $imgWidth = $this->_scopeConfig->getValue('iksanika_productmanage/images/width')."px";
        
        if($this->_helper->colIsVisible('thumbnail'))
        {
            $this->addColumn('thumbnail',
                [
                    'header'=> __('Thumbnail'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'thumbnail',
                    'renderer' => 'Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Image',
                ]
            );
        }
        
        if($this->_helper->colIsVisible('small_image')) 
        {
            $this->addColumn('small_image',
                [
                    'header'=> __('Small Img'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'small_image',
                    'renderer' => 'Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Image',
                ]
            );
        }
        
        if($this->_helper->colIsVisible('image')) 
        {
            $this->addColumn('image',
                [
                    'header'=> __('Image'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'image',
                    'renderer' => 'Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Image',
                ]
            );
        }
        if($this->_helper->colIsVisible('name')) 
        {
            $this->addColumn(
                'name',
                [
                    'header' => __('Name'),
                    'index' => 'name',
                    'class' => 'admin__control-text',
                    'type' => 'input',
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input',
                ]
            );

            if ($store->getId()) {
                $this->addColumn(
                    'custom_name',
                    [
                        'header' => __('Name in %1', $store->getName()),
                        'index' => 'custom_name',
//                        'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input',
                        'header_css_class' => 'col-name',
                        'column_css_class' => 'col-name'
                    ]
                );
            }
        }

        if($this->_helper->colIsVisible('type_id')) 
        {
            $this->addColumn(
                'type',
                [
                    'header' => __('Type'),
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => $this->_type->getOptionArray()
                ]
            );
        }
        
        if($this->_helper->colIsVisible('attribute_set_id')) 
        {
            $sets = $this->_setsFactory->create()->setEntityTypeFilter(
                $this->_productFactory->create()->getResource()->getTypeId()
            )->load()->toOptionHash();

            $this->addColumn(
//                'set_name',
                'attribute_set_id',
                [
                    'header' => __('Product Template'),
                    'index' => 'attribute_set_id',
                    'type' => 'options',
                    'options' => $sets,
                    'renderer' => "\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options",
//                    'header_css_class' => 'col-attr-name',
//                    'column_css_class' => 'col-attr-name'
                ]
            );
        }
        
        if($this->_helper->colIsVisible('sku')) 
        {
            $this->addColumn(
                'sku',
                [
                    'header' => __('SKU'),
                    'index' => 'sku',
                    'filter' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Filter\Sku',
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input',
                    'type' => 'input',
                ]
            );
        }
        
        if($this->_helper->colIsVisible('category_ids')) 
        {
            $this->addColumn('category_ids',
                array(
                    'header'=> __('Category ID\'s'),
                    'index' => 'category_ids',
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input',
                    'type' => 'input'
            ));
        }
        
        
        
        
        
        if($this->_helper->colIsVisible('category')) 
        {
            $this->addColumn('category',
                array(
                    'header' => __('Categories'),
                    'index' => 'category',
                    //'sortable' => false,
//                    'filter' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Filter\Category',
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Category',
                    'type' => 'options',
                    'options' => $this->_helperCategory->getOptionsForFilter(),
            ));
        }

        
        if($this->_helper->colIsVisible('price')) 
        {
            $store = $this->_getStore();
            $this->addColumn(
                'price',
                [
                    'header' => __('Price'),
                    'type' => 'price',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'price',
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Price',
                    'header_css_class' => 'col-price',
                    'column_css_class' => 'col-price'
                ]
            );
        }

        
        
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            if($this->_helper->colIsVisible('qty')) 
            {
                $this->addColumn(
                    'qty',
                    [
                        'header' => __('Quantity'),
                        'type' => 'number',
                        'index' => 'qty',
                        'filter' => '\Magento\Backend\Block\Widget\Grid\Column\Filter\Range',
                        'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Number',
                    ]
                );
            }
            if($this->_helper->colIsVisible('is_in_stock')) 
            {
                $columnIsInStock = [
                        'header' => __('Is in stock'),
                        'index' => 'is_in_stock',
                        'type' => 'options',
                        'options' => $this->_availability->getOptionArray(),
                        'header_css_class' => 'col-visibility',
                        'column_css_class' => 'col-visibility',
//                        'renderer' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Select\Extended',
                ];
                if(!$this->_scopeConfig->getValue('iksanika_productmanage/stockmanage/autoStockStatus'))
                {
                    $columnIsInStock['renderer'] = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options';
                }
                $this->addColumn('is_in_stock', $columnIsInStock);
            }
        }

        if($this->_helper->colIsVisible('visibility')) 
        {
            $this->addColumn(
                'visibility',
                [
                    'header' => __('Visibility'),
                    'index' => 'visibility',
                    'type' => 'options',
                    'options' => $this->_visibility->getOptionArray(),
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options',
                    'header_css_class' => 'col-visibility',
                    'column_css_class' => 'col-visibility'
                ]
            );
        }

        if($this->_helper->colIsVisible('status')) 
        {
            $this->addColumn(
                'status',
                [
                    'header' => __('Status'),
                    'index' => 'status',
                    'type' => 'options',
                    'options' => $this->_status->getOptionArray(),
                    'renderer' => '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options',
                ]
            );
        }

        if($this->_helper->colIsVisible('websites')) 
        {
            if (!$this->_storeManager->isSingleStoreMode()) {
                $this->addColumn(
                    'websites',
                    [
                        'header' => __('Websites'),
                        'sortable' => false,
                        'index' => 'websites',
                        'type' => 'options',
                        'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                        'header_css_class' => 'col-websites',
                        'column_css_class' => 'col-websites'
                    ]
                );
            }
        }

        
        
        

        
        
        
        
        
        
        
        
        
        
        
        
        
        $ignoreCols = array(
            'entity_id'=>true, 
            'websites'=>true,
            'status'=>true,
            'visibility'=>true,
            'qty'=>true,
            'is_in_stock'=>true,
            'price'=>true,
            'sku'=>true,
            'attribute_set_id'=>true, 
            'type_id'=>true,
            'name'=>true, 
            'image'=>true, 
            'thumbnail' => true, 
            'small_image'=>true,
            'category_ids' => true,
            'category' => true,
        );
        
        $currency = $store->getBaseCurrency()->getCode();
        
        $taxClassCollection = $this->_taxClassSourceProduct->toOptionArray();
        $taxClasses = array();
        foreach($taxClassCollection as $taxClassItem)
            $taxClasses[$taxClassItem['value']] = $taxClassItem['label'];

        $storeId = $this->_helper->getStoreId();
        $store = $this->_helper->getStore();
        
        
        $attributes = $this->_helper->getAttributesList();
        
        $typeList = array();
        
        foreach($attributes as $attribute)
        {
            $typeList[$attribute->getFrontendInput()] = true;
        }
        
        // associate array (hashmap) attribute code -> attribute
        $attrs    =   array();
        foreach($attributes as $attribute)
        {
            $attrs[$attribute->getAttributeCode()]  =   $attribute;
        }
        

        
//        foreach(self::$columnSettings as $code => $true) 
        foreach($this->_helper->getColumnSettings() as $code => $true) 
        {
            $column = array();
            
            if(isset($ignoreCols[$code])) 
                continue;
            
            if($code != 'related_ids' && $code != 'cross_sell_ids' && $code != 'up_sell_ids' && 
                $code != 'associated_groupped_ids' && $code != 'associated_configurable_ids')
            {
                $column['index']    =   $code;
                $column['header']   =   __($attrs[$code]->getStoreLabel());
                $column['width']    =   '100px';
                
                // @TODO: temporary - need to enable Widget_Column class to reassign on the fly
                if($attrs[$code]->getFrontendInput() == 'input' || $attrs[$code]->getFrontendInput() == 'text')
                {
                    $column['renderer'] = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input';
                }
                //
                
                // redefine editable type and renderers to static columns type
                if($attrs[$code]->getFrontendInput() == 'text')
                {
                    $column['type']    =   'input';
                }
                
                if ($attrs[$code]->getFrontendInput() == 'weight')
                {
                    $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Number';
                }
                
                if ($attrs[$code]->getFrontendInput() == 'price')
                {
                    $column['filter']         =   '\Magento\Backend\Block\Widget\Grid\Column\Filter\Range';
                    $column['renderer']         =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Price';
                    $column['currency_code']    =   $currency;
                }
                
                if($attrs[$code]->getFrontendInput() == 'textarea')
                {
                    $column['type']     =   'iks_textarea';
                    $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Textarea';
                }
                
                if($attrs[$code]->getFrontendInput() == 'date')
                {
                    $column['type'] = 'datetime';
                    
                    $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Datepicker';
//                    $column['image']    =   Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif';
//                    $column['format']   =   Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                }
                
                if($attrs[$code]->getFrontendInput() == 'media_image')
                {
                    $column['width']    =   $imgWidth;
                    $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Image';
                }
                
                if ($attrs[$code]->getFrontendInput() == 'multiselect')
                {
                    $column['renderer'] = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Multiselect';
                    $column['filter']   = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Filter\Multiselect';
                }
                
                if ($attrs[$code]->getFrontendInput() == 'select')
                {
                    $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options';
                }

                $column['width'] = '100px';

                // load options for lists
                if (
                        $attrs[$code]->getFrontendInput() == 'select' || 
                        $attrs[$code]->getFrontendInput() == 'multiselect' || 
                        $attrs[$code]->getFrontendInput() == 'boolean')
                {
                    $attrOptions = array();
                    
                    if ($attrs[$code]->getAttributeCode() == 'custom_design')
                    {
                        $allOptions = $attrs[$code]->getSource()->getAllOptions();
                        if (is_array($allOptions) && !empty($allOptions))
                        {
                            foreach ($allOptions as $option)
                            {
                                if (!is_array($option['value']))
                                {
                                    if ($option['value'])
                                    {
                                        $attrOptions[$option['value']] = $option['value'];
                                    }
                                } else
                                {
                                    foreach ($option['value'] as $option2)
                                    {
                                        if (isset($option2['value']))
                                        {
                                            $attrOptions[$option2['value']] = $option2['value'];
                                        }
                                    }
                                }
                            }
                        }
                    } else
                    {
                        // getting attribute values with translation
                        $valuesCollection = clone $this->_eavEntityOptCollection;
                        $valuesCollection->setAttributeFilter($attrs[$code]->getId())
                            ->setStoreFilter($this->_helper->getStoreId(), false)
                            ->load();
                        //$valuesCollection->printLogQuery(true);

                        if ($valuesCollection->getSize() > 0)
                        {
                            foreach ($valuesCollection as $item)
                            {
                                $attrOptions[$item->getId()] = $item->getValue();
                            }
                        } else
                        {
                            $selectOptions = $attrs[$code]->getFrontend()->getSelectOptions();
                            if ($selectOptions)
                            {
                                foreach ($selectOptions as $selectOption)
                                {
                                    $attrOptions[$selectOption['value']] = $selectOption['label'];
                                }
                            }
                        }
                    }

                    $column['type'] = 'options';
                    $column['options'] = $attrOptions;
                    if($attrs[$code]->getFrontendInput() == 'multiselect')
                    {
                        $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Multiselect';
                    }else
                    {
                        $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Options';
                    }
                }

                if($attrs[$code])
                {
                    $column['attribute'] = $attrs[$code];
                }

                if ($attrs[$code]->getAttributeCode() == 'tier_price')
                {
//                    $column['renderer'] = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\TierPrice';
                    $column['renderer'] = '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Text';
                    $column['type'] = 'text';
                    $column['sortable'] = false;
                }

                if ($attrs[$code]->getAttributeCode() == 'group_price')
                {
//                    $column['renderer'] = '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\GroupPrice';
                    $column['renderer'] = '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Text';
                    $column['type'] = 'text';
                    $column['sortable'] = false;
                }
            }else
            {
                $column['index']    =   $code;
                $column['header']   =   __($code);
                $column['width']    =   '100px';
                $column['renderer'] =   '\Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer\Input';
            }
            
            // add column to grid
            $this->addColumn($code, $column);
        }
        
        
        
        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'catalog/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        
        $this->addColumn(
            'view', 
            [
                'header' => __('View'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '../catalog/product/view/view',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'id',
						"target"=> "_blank"
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'link_view',
            ]
        );
        
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        
        $this->addExportType('productmanage/*/exportCsv', __('CSV'));
        $this->addExportType('productmanage/*/exportXml', __('XML'));
        
        return parent::_prepareColumns();
    }
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $_auth = $this->_authorization;
        if($_auth->isAllowed('Iksanika_Productmanage::actions'))
        {

            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setTemplate('Iksanika_Productmanage::widget/grid/massaction_extended.phtml');
            $this->getMassactionBlock()->setFormFieldName('product');

            if($_auth->isAllowed('Iksanika_Productmanage::ma_delete'))
            {
                $this->getMassactionBlock()->addItem(
                    'delete',
                    [
                        'label' => __('Delete'),
                        'url' => $this->getUrl('productmanage/*/defaultMassDelete'),
                        'confirm' => __('Are you sure?')
                    ]
                );
            }

            if($_auth->isAllowed('Iksanika_Productmanage::ma_change_status'))
            {
                $statuses = $this->_status->getOptionArray();

                array_unshift($statuses, ['label' => '', 'value' => '']);
                $this->getMassactionBlock()->addItem(
                    'status',
                    [
                        'label' => __('Change Status'),
                        'url' => $this->getUrl('productmanage/*/defaultMassStatus', ['_current' => true]),
                        'additional' => [
                            'visibility' => [
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => __('Status'),
                                'values' => $statuses
                            ]
                        ]
                    ]
                );
            }

            if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_attributes')) {
                $this->getMassactionBlock()->addItem(
                    'attributes',
                    [
                        'label' => __('Update Attributes'),
                        'url' => $this->getUrl('productmanage/product_action_attribute/edit', ['_current' => true])
                    ]
                );
            }


            $this->getMassactionBlock()->addItem('otherDivider', $this->getSubDivider("------Additional------"));
    //        $this->getMassactionBlock()->addItem('otherDivider', $this->getDivider("Category"));

            /*
             * Prepare list of column for update
             */
            $fields = $this->_helper->getColumnForUpdate();

            if ($_auth->isAllowed('Iksanika_Productmanage::ma_update')) 
            {
                $this->getMassactionBlock()->addItem('save', 
                    [
                        'label' => __('Update'),
                        'url'   => $this->getUrl('productmanage/*/massUpdateProducts', ['_current'=>true, '_query' => '']),
                        'fields' => $fields
                    ]
                );
                
            }
    //        $this->getMassactionBlock()->addItem('massActionDevider', $this->getDivider("Mass Actions"));


            if ($_auth->isAllowed('Iksanika_Productmanage::ma_duplicate')) 
            {
                $this->getMassactionBlock()->addItem('duplicate', 
                    [
                        'label'     =>  __('Duplicate'),
                        'url'       =>  $this->getUrl('productmanage/*/duplicateProducts', array('_current'=>true)),

                        'css'       =>  'tau-clone',
                        'notice'    =>  __('Make a copy of selected products.'),
                    ]
                );
            }
    /*        
            $this->getMassactionBlock()->addItem('attributeSet', 
                array(
                    'label'         =>  __('Change Attribute Set'),
                    'url'           =>  $this->getUrl('productmanage /* /changeAttributeSetProducts', ['_current'=>true]),
                    'additional'    =>  $this->getAttributeSets(__('To: ')),
                    'notice' => __('Change products attributes set to new selected one.'),
                    'uititle'  =>  __('Change Attribute Set'),
                    'uinotice' =>  __('Change products attributes set to new selected one.'),
                )
            );
    */        
            if ($_auth->isAllowed('Iksanika_Productmanage::ma_category_add') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_category_remove') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_category_replace')) 
            {
            
                $this->getMassactionBlock()->addItem('categoryActionDivider', $this->getCleanDivider());
                $this->getMassactionBlock()->addItem('otherCategoryActionDivider', $this->getDivider("Category"));

                if($_auth->isAllowed('Iksanika_Productmanage::ma_category_add')) 
                {
                    $this->getMassactionBlock()->addItem('addCategory', 
                        array(
                            'label'     =>  __('Category: Add'),
                            'url'       =>  $this->getUrl('productmanage/*/categoryAdd', ['_current'=>true]),
                            'additional'=>  $this->getCategoriesTree(__('Category IDs ')),
                            'css'       =>  'tau-category',
                            'notice'    =>  __('Assign selected products to selected categories.'),
                            'uititle'  =>  __('Category: Add'),
                            'uinotice' =>  __('Assign selected products to selected categories.'),
                        )
                    );
                }

                if($_auth->isAllowed('Iksanika_Productmanage::ma_category_remove')) 
                {
                    $this->getMassactionBlock()->addItem('removeCategory',
                        array(
                            'label'     =>  __('Category: Remove'),
                            'url'       =>  $this->getUrl('productmanage/*/categoryRemove', ['_current'=>true]),
                            'additional'=>  $this->getCategoriesTree(__('Category IDs ')),
                            'css'       =>  'tau-category',
                            'notice'    =>  __('Unassign selected products from selected categories.'),
                            'uititle'  =>  __('Category: Remove'),
                            'uinotice' =>  __('Unassign selected products from selected categories.'),
                        )
                    );
                }

                if($_auth->isAllowed('Iksanika_Productmanage::ma_category_replace')) 
                {
                    $this->getMassactionBlock()->addItem('replaceCategory', 
                        array(
                            'label'     =>  __('Category: Replace'),
                            'url'       =>  $this->getUrl('productmanage/*/categoryReplace', ['_current'=>true]),
                            'additional'=>  $this->getCategoriesTree(__('Category IDs ')),
                            'css'       =>  'tau-category',
                            'notice'    =>  __('Unassign products from all categories and assign this products to specified list of cateogires.'),
                        )
                    );
                }
            }




            if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_qty') || 
                $_auth->isAllowed('Iksanika_Productmanage::ma_update_is_in_stock'))
            {

                $this->getMassactionBlock()->addItem('stockActionDivider', $this->getCleanDivider());
                $this->getMassactionBlock()->addItem('otherStockActionDivider', $this->getDivider("Stock"));

                    /*
                     * Prepare list of column for update
                     */

                if($_auth->isAllowed('Iksanika_Productmanage::ma_update_qty')) 
                {
                    $this->getMassactionBlock()->addItem(
                        'updateQty', 
                        [
                            'label'     =>  __('Update: Qty'),
                            'url'       =>  $this->getUrl('productmanage/*/updateQty', ['_current' => true]),
                            'additional'=>  $this->getQtyField(__('Qty: ')),
                        ]
                    );
                }

                if($_auth->isAllowed('Iksanika_Productmanage::ma_update_is_in_stock')) 
                {
                    $statuses = $this->_availability->getOptionArray();
                    $this->getMassactionBlock()->addItem(
                        'updateQtyStatus',
                        [
                            'label' => __('Update: Is In Stock'),
                            'url' => $this->getUrl('productmanage/*/updateQtyStatus', ['_current' => true]),
                            'additional' => [
                                'visibility' => [
                                    'name' => 'is_in_stock',
                                    'type' => 'select',
                                    'class' => 'required-entry',
                                    'label' => __('Is in Stock: '),
                                    'values' => $statuses
                                ]
                            ]
                        ]
                    );
                }
            }













            if($_auth->isAllowed('Iksanika_Productmanage::ma_update_price') ||
               $_auth->isAllowed('Iksanika_Productmanage::ma_update_cost') ||
               $_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price') ||
               $_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price_on_price') ||
               $_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price_on_cost'))
            {

                $this->getMassactionBlock()->addItem('priceActionDivider', $this->getCleanDivider());
                $this->getMassactionBlock()->addItem('otherPriceActionDivider', $this->getDivider("Prices"));


                    /*
                     * Prepare list of column for update
                     */
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_price'))
                {

                    $this->getMassactionBlock()->addItem(
                        'updatePrice', 
                        [
                            'label'     =>  __('Update: Price'),
                            'url'       =>  $this->getUrl('productmanage/*/updatePrice', ['_current' => true]),
                            'additional'=>  $this->getPriceField(__('By: '))
                        ]
                    );
                }
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_cost'))
                {
                    $this->getMassactionBlock()->addItem('updateCost', 
                        [
                            'label'     =>  __('Update: Cost'),
                            'url'       =>  $this->getUrl('productmanage/*/updateCost', ['_current'=>true]),
                            'additional'=>  $this->getPriceField(__('By: ')),
                        ]
                    );
                }
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price'))
                {
                    $this->getMassactionBlock()->addItem('updateSpecialPrice', 
                        [
                            'label'     =>  __('Update: Special Price'),
                            'url'       =>  $this->getUrl('productmanage/*/updateSpecialPrice', ['_current'=>true]),
                            'additional'=>  $this->getPriceField(__('By: ')),
                        ]
                    );
                }
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_price_on_cost'))
                {
                    $this->getMassactionBlock()->addItem('updatePriceByCost', 
                        [
                            'label'     =>  __('Update: Price based on Cost'),
                            'url'       =>  $this->getUrl('productmanage/*/updatePriceByCost', ['_current'=>true]),
                            'additional'=>  $this->getPriceField(__('By: ')),
                        ]
                    );
                }
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price_on_cost'))
                {
                    $this->getMassactionBlock()->addItem('updateSpecialPriceByCost', 
                        [
                            'label'     =>  __('Update: Special Price based on Cost'),
                            'url'       =>  $this->getUrl('productmanage/*/updateSpecialPriceByCost', ['_current'=>true]),
                            'additional'=>  $this->getPriceField(__('By: ')),
                        ]
                    );
                }
                if ($_auth->isAllowed('Iksanika_Productmanage::ma_update_special_price_on_price'))
                {
                    $this->getMassactionBlock()->addItem('updateSpecialPriceByPrice', 
                        [
                            'label'     =>  __('Update: Special Price based on Price'),
                            'url'       =>  $this->getUrl('productmanage/*/updateSpecialPriceByPrice', ['_current'=>true]),
                            'additional'=>  $this->getPriceField(__('By: ')),
                        ]
                    );
                }
            }






            if ($_auth->isAllowed('Iksanika_Productmanage::ma_related_to_each_other') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_related_to_add') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_related_to_clear') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_each_other') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_add') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_clear') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_add') ||
                $_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_clear'))
            {

                if ($_auth->isAllowed('Iksanika_Productmanage::ma_related_to_each_other') ||
                    $_auth->isAllowed('Iksanika_Productmanage::ma_related_to_add') ||
                    $_auth->isAllowed('Iksanika_Productmanage::ma_related_to_clear'))
                {

                    $this->getMassactionBlock()->addItem('relatorActionDivider', $this->getCleanDivider());
                    $this->getMassactionBlock()->addItem('productRelatorActionDivider', $this->getDivider("Product Relator"));




                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_related_to_each_other'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'relatedEachOther', [
                                'label' => __('Related: To Each Other'),
                                'url'   => $this->getUrl('*/*/massRelatedEachOther', ['_current'=>true]),
                                'callback' => 'specifyRelatedEachOther()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Relate selected products to each other.'),
                            ]
                        );
                    }
                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_related_to_add'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'relatedTo', [
                                'label' => __('Related: Add ..'),
                                'url'   => $this->getUrl('*/*/massRelatedTo', ['_current'=>true]),
                                'callback' => 'specifyRelatedProducts()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Relate specified products to selected list of products.'),
                            ]
                        );
                    }
                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_related_to_clear'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'relatedClean', [
                                'label' => __('Related: Clear'),
                                'url'   => $this->getUrl('*/*/massRelatedClean', ['_current'=>true]),
                                'callback' => 'specifyRelatedClean()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Remove all related products from selected list of products.'),
                            ]
                        );
                    }
                }



                if ($_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_each_other') ||
                    $_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_add') ||
                    $_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_clear'))
                {
                    $this->getMassactionBlock()->addItem('crossSellActionDivider', $this->getCleanDivider());

                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_each_other'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'crossSellEachOther', [
                                'label' => __('Cross-Sell: To Each Other'),
                                'url'   => $this->getUrl('*/*/massCrossSellEachOther', ['_current'=>true]),
                                'callback' => 'specifyCrossSellEachOther()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Cross-sell selected products to each other.'),
                            ]
                        );
                    }
                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_add'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'crossSellTo', [
                                'label' => __('Cross-Sell: Add ..'),
                                'url'   => $this->getUrl('*/*/massCrossSellTo', ['_current'=>true]),
                                'callback' => 'chooseWhatToCrossSellTo()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Cross-sell specified products to selected list of products.'),
                            ]
                        );
                    }
                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_cross_sell_to_clear'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'crossSellClear', [
                                'label' => __('Cross-Sell: Clear'),
                                'url'   => $this->getUrl('*/*/massCrossSellClean', ['_current'=>true]),
                                'callback' => 'specifyCrossSellClean()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Remove all cross-sells products from selected list of products.'),
                            ]
                        );
                    }
                }

                if($_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_add') ||
                   $_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_clear'))
                {
                    $this->getMassactionBlock()->addItem('upSellActionDivider', $this->getCleanDivider());

                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_add'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'upSellTo', [
                                'label' => __('Up-Sells: Add ..'),
                                'url'   => $this->getUrl('*/*/massUpSellTo', ['_current'=>true]),
                                'callback' => 'chooseWhatToUpSellTo()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Up-sell specified products to selected list of products.'),
                            ]
                        );
                    }
                    if ($_auth->isAllowed('Iksanika_Productmanage::ma_up_sells_clear'))
                    {
                        $this->getMassactionBlock()->addItem(
                            'upSellClear', [
                                'label' => __('Up-Sells: Clear'),
                                'url'   => $this->getUrl('*/*/massUpSellClean', ['_current'=>true]),
                                'callback' => 'specifyUpSellClean()',
                                'css'       =>  'tau-relate',
                                'notice'    =>  __('Remove all up-sell products from selected list of products.'),
                            ]
                        );
                    }
                }
            }
        }
        $this->_eventManager->dispatch('adminhtml_catalog_product_grid_prepare_massaction', ['block' => $this]);
        return $this;
    }
    
    protected function getCategoriesTree($title)
    {
        $element = [
            'category_value' => [
                'name'  =>  'category',
                'type'  =>  'text',
                'class' =>  'required-entry',
                'label' =>  $title,
            ]
        ];

//        if($this->_scopeConfig->getValue('productmanage/massactions/categorynames', $this->_helper->getStoreId()))
        if($this->_scopeConfig->getValue('iksanika_productmanage/massactions/categorynames'))
        {
            $rootId = $this->_helper->getStore()->getRootCategoryId();
            $element['category_value']['label']   =   __('Category');
            $element['category_value']['type']    =   'select';
            $element['category_value']['values']  =   $this->_helperCategory->getTree($rootId);
        } 
        
        return $element;      
    } 
    
    protected function getAttributeSets($title)
    {
        $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();
            
        $element = [
            'attribute_set_value' => [
                'name'      =>  'attribute_set',
                'type'      =>  'select',
                'class'     =>  'required-entry',
                'label'     =>  __($title),
                'values'    =>  $sets,
//                'values'=>  Mage::getResourceModel('eav/entity_attribute_set_collection')
//                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
//                    ->load()->toOptionHash(),
            ]
        ];
        
        return $element;      
    } 
     
    protected function getPriceField($title, $field = 'price')
    {
        $element = [
            'price_value' => [
                'name'  =>  'price',
                'type'  =>  'text',
                'class' =>  ($field != 'cost' && $field != 'special_price') ? 'required-entry' : '',
                'label' =>  $title,
            ]
        ];
        
        return $element;      
    } 
    
    protected function getProductField($title, $field = 'product')
    {
        $element = [
            'product_value' => [
                'name'  =>  $field,
                'type'  =>  'text',
                'class' =>  'required-entry',
                'label' =>  $title,
            ]
        ];
        
        return $element;      
    } 
    
    protected function getQtyField($title, $field = 'qty')
    {
        $element = [
            'qty_value' => [
                'name'  =>  $field,
                'type'  =>  'text',
                'class' =>  'required-entry',
                'label' =>  $title,
            ]
        ];
        
        return $element;      
    } 
    
    protected function getDivider($divider="*******") 
    {
        $dividerTemplate = [
            'label' => '********'.__($divider).'********',
            'url'   => $this->getUrl('productmanage/*/index', ['_current'=>true]),
            'callback' => "null"
        ];
        return $dividerTemplate;
    }

    protected function getSubDivider($divider="-------") {
        $dividerTemplate = [
          'label' => '--------'.__($divider).'--------',
          'url'   => $this->getUrl('productmanage/*/index', ['_current'=>true]),
          'callback' => "null"
        ];
        return $dividerTemplate;
    }

    protected function getCleanDivider() {
        $dividerTemplate = [
          'label' => ' ',
          'url'   => $this->getUrl('productmanage/*/index', ['_current'=>true]),
          'callback' => "null"
        ];
        return $dividerTemplate;
    }
    

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('productmanage/*/grid', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
    
    
    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $data = array();
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";
        
        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) 
                {
                    $colIndex = $column->getIndex();
                    $colContent = $item->getData($colIndex);
//                    if($colIndex == 'category_ids')
//                        $colContent = implode(',', $item->getCategoryIds());
                    if(is_array($colContent) || $colIndex == 'category_ids')
                        $colContent = implode(',', $item->getCategoryIds());
                    $data[] = '"'.str_replace(array('"', '\\'), array('""', '\\\\'), $colContent).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        if ($this->getCountTotals())
        {
            $data = array();
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'),
                        $column->getRowFieldExport($this->getTotals())) . '"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }
        return $csv;
    }
    
}
