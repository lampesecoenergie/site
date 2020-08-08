<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Block\Adminhtml\Post\Edit\Tab;

class Related extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
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

    protected $_postFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context
     * @param \Magento\Backend\Helper\Data
     * @param \Magento\Catalog\Model\Product\LinkFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory
     * @param \Magento\Catalog\Model\Product\Type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status
     * @param \Magento\Catalog\Model\Product\Visibility
     * @param \Magento\Framework\Registry
     * @param \Ves\Blog\Model\Post
     * @param array
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Registry $coreRegistry,
        \Ves\Blog\Model\Post $postFactory,
        array $data = []
        ) {
        $this->_linkFactory = $linkFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_coreRegistry = $coreRegistry;
        $this->_postFactory = $postFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('related_product_grid');
        $this->setDefaultSort('post_id');
        $this->setUseAjax(true);
        if ($this->getPost() && $this->getPost()->getPostId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * Retirve currently edited product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getPost()
    {
        return $this->_coreRegistry->registry('current_post');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('post_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('post_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $post = $this->getPost();
        $collection = $this->_postFactory->getCollection();

        $collection->addFieldToFilter('post_id', ['neq' => $post->getId()]);

        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = [0];
            }
            $collection->addFieldToFilter('post_id', ['in' => $productIds]);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
           'in_products',
           [
               'type'             => 'checkbox',
               'name'             => 'in_products',
               'values'           => $this->_getSelectedProducts(),
               'align'            => 'center',
               'index'            => 'post_id',
               'header_css_class' => 'col-select',
               'column_css_class' => 'col-select'
           ]
         );

        $this->addColumn(
            'gpost_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'post_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
            );
        $this->addColumn(
            'gtitle',
            [
                'header'           => __('Title'),
                'index'            => 'title',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
            );

        $this->addColumn(
            'gidentifier',
            [
                'header'           => __('Identifier'),
                'index'            => 'identifier',
                'style'            => 'width:100px;',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
            );

        $this->addColumn(
            'gis_active',
            [
                'header'           => __('Status'),
                'index'            => 'is_active',
                'type'             => 'options',
                'options'          => $this->_status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
            );

        $this->addColumn(
            'gaction',
            [
                'header'   => __('Action'),
                'type'     => 'action',
                'renderer' => 'Ves\Blog\Block\Adminhtml\Post\Renderer\PostAction'
            ]
            );

        $this->addColumn(
            'position',
            [
                'header'                    => __('Position'),
                'name'                      => 'position',
                'type'                      => 'number',
                'validate_class'            => 'validate-number',
                'index'                     => 'position',
                'header_css_class'          => 'col-position',
                'column_css_class'          => 'col-position',
                'editable'                  => true,
                'edit_only'                 => true,
                'sortable'                  => false,
                'filter_condition_callback' => [$this, 'filterProductPosition']
            ]
            );

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        $post = $this->getPost();
        return $this->_getData(
            'grid_url'
            ) ? $this->_getData(
            'grid_url'
            ) : $this->getUrl(
            'vesblog/post/relatedGrid/post_id/'.$post->getPostId(),
            ['_current' => true]
            );
        }

        protected function _getSelectedProducts()
        {
            $products = $this->getProductsUpsell();
            if (!is_array($products)) {
                $products = array_keys($this->getSelectedPosts());
            }
            return $products;
        }

        public function getSelectedPosts()
        {
            $products = [];
            $post = $this->_coreRegistry->registry('current_post');
            if($post){
                $postsRelated = $post->getPostsRelated();

                if(!empty($postsRelated)){
                    foreach ($postsRelated as $_post) {
                        $products[$_post['post_related_id']] = ['position' => $_post['position']];
                    }
                }
            }
            return $products;
        }

    /**
     * Apply `position` filter to cross-sell grid.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @return $this
     */
    public function filterProductPosition($collection, $column)
    {
        $collection->addLinkAttributeToFilter($column->getIndex(), $column->getFilter()->getCondition());
        return $this;
    }
}
