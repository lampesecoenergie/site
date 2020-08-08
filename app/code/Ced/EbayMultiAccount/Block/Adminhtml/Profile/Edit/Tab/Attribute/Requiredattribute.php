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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class Requiredattribute
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Requiredattribute extends Widget implements RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_EbayMultiAccount::profile/attribute/required_attribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected  $_coreRegistry;
    /**
     * @var mixed
     */
    protected  $_profile;
    /**
     * @var
     */
    protected  $_ebaymultiaccountAttribute;

    /**
     * Requiredattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []

    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return array
     */
    public function getEbayMultiAccountAttributes()
    {
        $listingType = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Source\ListingType')->getLabel();
        $listingDuration = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Source\ListingDuration')->getLabel();
        $requiredAttribute = [
            'Product Name' => ['ebaymultiaccount_attribute_name' => 'name','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => '','magento_attribute_code' => 'name', 'required' => 1],
            'SKU' => ['ebaymultiaccount_attribute_name' => 'sku','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => '','magento_attribute_code' => 'sku', 'required' => 1],
            'Description' => ['ebaymultiaccount_attribute_name' => 'description','ebaymultiaccount_attribute_type' => 'textarea', 'ebaymultiaccount_attribute_enum' => '','magento_attribute_code' => 'description', 'required' => 1],
            'Inventory And Stock' => ['ebaymultiaccount_attribute_name' => 'inventory','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => '','magento_attribute_code' => 'quantity_and_stock_status', 'required' => 1],
            'Maximum Dispatch Time' => ['ebaymultiaccount_attribute_name' => 'max_dispatch_time','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => implode(',', array(0, 1, 2, 3, 4, 5, 10, 15, 20, 30)),'magento_attribute_code' => '', 'required' => 1],
            'Listing Type' => ['ebaymultiaccount_attribute_name' => 'listing_type','ebaymultiaccount_attribute_type' => 'select', 'ebaymultiaccount_attribute_enum' => implode(',', $listingType),'magento_attribute_code' => '', 'required' => 1],
            'Listing Duration' => ['ebaymultiaccount_attribute_name' => 'listing_duration','ebaymultiaccount_attribute_type' => 'select', 'ebaymultiaccount_attribute_enum' => implode(',', $listingDuration),'magento_attribute_code' => '', 'required' => 1]
            ];
        $optionalAttribues = [
            'UPC' => ['ebaymultiaccount_attribute_name' => 'upc','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => ''],
            'EAN' => ['ebaymultiaccount_attribute_name' => 'ean','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => ''],
            'ISBN' => ['ebaymultiaccount_attribute_name' => 'isbn','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => ''],
            'BestOfferEnabled' => ['ebaymultiaccount_attribute_name' => 'bestofferenabled','ebaymultiaccount_attribute_type' => "boolean", 'ebaymultiaccount_attribute_enum' => 'false,true'],
            'Auto Pay' => ['ebaymultiaccount_attribute_name' => 'auto_pay','ebaymultiaccount_attribute_type' => "boolean", 'ebaymultiaccount_attribute_enum' => 'false,true'],
            'Brand' => ['ebaymultiaccount_attribute_name' => 'brand','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => ''],
            'Manufacturer Part Number' => ['ebaymultiaccount_attribute_name' => 'manufacturer_part_number','ebaymultiaccount_attribute_type' => 'text', 'ebaymultiaccount_attribute_enum' => ''],
            'Bullets' => ['ebaymultiaccount_attribute_name' => 'bullets','ebaymultiaccount_attribute_type' => 'textarea', 'ebaymultiaccount_attribute_enum' => ''],
            ];
        
        $this->_ebaymultiaccountAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


        $this->_ebaymultiaccountAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );
        return $this->_ebaymultiaccountAttribute;
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_ebaymultiaccountAttribute[0]['value'];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileReqOptAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
        return $data;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
