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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab;

/**
 * Class Products
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var string
     */
    protected $_massactionBlockName = 'Magento\Backend\Block\Widget\Grid\Massaction\Extended';

    /**
     * Products constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setId('groupVendorPpcode');
        $this->_massactionBlockName = 'Ced\EbayMultiAccount\Block\Adminhtml\Profile\Widget\Grid\Massaction\Extended';
        $this->setDefaultFilter(['massaction' => 1]);
        $this->setUseAjax(true);

    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'massaction') {
            $inProfileIds = $this->getProducts();
            $inProfileIds = array_filter($inProfileIds);
            if (empty($inProfileIds)) {
                $inProfileIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $inProfileIds));
            } else {
                if ($inProfileIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $inProfileIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $profileAccount = $this->_coreRegistry->registry('ebay_account');
        $profileCode = $this->getRequest()->getParam('pcode');
        $this->_coreRegistry->register('PCODE', $profileCode);

        $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->setStoreId((int) $profileAccount->getAccountStore())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', array('neq' => 1))
            ->addAttributeToFilter('type_id', ['simple', 'configurable']);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $currentAccount = $this->_coreRegistry->registry('ebay_account');
        $this->addColumn('entity_id', array(
            'header' => __('Product Id'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'entity_id',
            'filter_index' => 'entity_id',
            'type' => 'number',
        ));

        $this->addColumn('sku', array(
            'header' => __('SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'sku',
        ));

        $this->addColumn('name', array(
            'header' => __('Product Name'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'name',
        ));
        $this->addColumn('type_id', [
                'header' => __('Type'),
                'align' => 'left',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_objectManager->get('Magento\Catalog\Model\Product\Type')->getOptionArray(),
                'header_css_class' => 'col-group',
                'column_css_class' => 'col-group'
            ]
        );

        $this->addColumn(
            'category',
            array(
                'header' => __('Category'),
                'index' => 'category',
                'sortable' => false,
                'type' => 'options',
                'options' => $this->_objectManager->create('Ced\EbayMultiAccount\Model\Source\Category')->getAllOptions(),
                'renderer' => 'Ced\EbayMultiAccount\Block\Adminhtml\Profile\Renderer\Category',
                'filter_condition_callback' => [$this, 'filterCallback'],
            ),
            'name'
        );
        $this->addColumn('status', array(
            'header' => __('Product Status'),
            'align' => 'left',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'options' => $this->_objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status')->getOptionArray(),
        ));

        $attributeSet = $this->_objectManager->get('Magento\Catalog\Model\Product\AttributeSet\Options')->toOptionArray();
        $values = [];
        foreach ($attributeSet as $val) {
            $values[$val['value']] = $val['label'];
        }

        $this->addColumn('set_name', array(
            'header' => __('Attribute Set Name'),
            'align' => 'left',
            'index' => 'attribute_set_id',
            'filter_index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $values,
        ));


        $store = $this->_storeManager->getStore();
        $this->addColumn('price', array(
            'header' => __('Price'),
            'align' => 'left',
            'type' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
            'filter_index' => 'price',
        ));
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/editProfileProductGrid', array('_secure' => true, '_current' => true));
    }

    /**
     * @param bool $json
     * @return array|string
     */
    public function getProducts($json = false)
    {
        $products = [];
        $profileCode = $this->getRequest()->getParam('pcode');
        $profile = $this->_coreRegistry->registry('current_profile');
        $currentAccount = $this->_coreRegistry->registry('ebay_account');
        $ids = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $profileCode)->getColumnValues('id');
        if (!empty($ids)) {
            $profileAccountAttr = $this->multiAccountHelper->getProfileAttrForAcc($currentAccount->getId());
            $products = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter($profileAccountAttr, $ids)->getColumnValues('entity_id');
        }
        if (sizeof($products) > 0) {
            if ($json) {
                $jsonProducts = Array();
                foreach ($products as $productId) $jsonProducts[$productId] = 0;
                return json_encode($jsonProducts);
            } else {
                return array_values($products);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return array();
            }
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function isPartUppercase($string)
    {
        return (bool)preg_match('/[A-Z]/', $string);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id[]');

        $this->getMassactionBlock()->addItem(
            'addproduct', array(
                'label' => __('Add Products'),
                'url' => $this->getUrl('ebaymultiaccount/profile/save'),
            )
        );
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        return $products;
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $collection->addCategoriesFilter(['in' => $value]);
        return $collection;
    }
}