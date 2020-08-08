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

use Ced\EbayMultiAccount\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class EbayMultiAccountattribute
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class EbayMultiAccountattribute extends Widget implements RendererInterface

{
    /**
     * @var string
     */
    public $_template = 'Ced_EbayMultiAccount::profile/attribute/ebaymultiaccountattribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    public  $_coreRegistry;
    /**
     * @var mixed
     */
    public  $_profile;
    /**
     * @var
     */
    public  $_ebaymultiaccountAttribute;
    /**
     * @var
     */
    public $_ebaymultiaccountAttributeFeature;
    /**
     * @var Data
     */
    public $helper;

    /**
     * EbayMultiAccountattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Data $helper,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->helper = $helper;

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
        )->setData(['label' => 'Add Attribute', 'onclick' =>'return ebaymultiaccountAttributeControl.addItem()', 'class' => 'add']);

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
        $catId =  $this->getCatId();
        $requiredAttributes = $optionalAttribues = [];
        if($this->_profile && $this->_profile->getId()>0) {
            $catArray = json_decode($this->_profile->getProfileCategory(), true);
            $data = array_reverse($catArray);
            if ($data) {
                foreach ($data as $value) {
                    if ($value != "") {
                        $catId = $value;
                        break;
                    }
                }
            }
        }
        if ($catId) {
            $getAttribute = $this->helper->getCatSpecificAttribute($catId);
            if (isset($getAttribute->Recommendations->NameRecommendation)) {
                $attributeCollections = $getAttribute->Recommendations->NameRecommendation;
                if (isset($attributeCollections->Name)) {
                    $enumRequired = $enumOptional = [];
                    if (isset($attributeCollections->ValidationRules->MinValues)) {
                        if (isset($attributeCollections->ValueRecommendation)) {
                            foreach ($attributeCollections->ValueRecommendation as $value) {
                                if (isset($value->Value)) {
                                    $enumRequired[] = $value->Value;
                                }
                            }                                
                        }
                        $requiredAttributes [$attributeCollections->Name] = [
                                'ebaymultiaccount_attribute_name' => $attributeCollections->Name, 'ebaymultiaccount_attribute_type' => $attributeCollections->ValidationRules->SelectionMode, 'ebaymultiaccount_attribute_enum' => implode(',', $enumRequired), 'magento_attribute_code' => '', 'required' => 1
                            ];
                    } else {
                        if (isset($attributeCollections->ValueRecommendation)) {
                            foreach ($attributeCollections->ValueRecommendation as $value) {
                                if (isset($value->Value)) {
                                    $enumOptional[] = $value->Value;
                                }
                            }
                        }
                        $optionalAttribues[$attributeCollections->Name] = [
                                'ebaymultiaccount_attribute_name' => $attributeCollections->Name, 'ebaymultiaccount_attribute_type' => $attributeCollections->ValidationRules->SelectionMode, 'ebaymultiaccount_attribute_enum' => implode(',', $enumOptional)
                            ];
                    }
                } else {
                    foreach ($attributeCollections as $item) {
                        $enumRequired = $enumOptional = [];
                        if (isset($item->ValidationRules->MinValues)) {
                            if (isset($item->ValueRecommendation)) {
                                foreach ($item->ValueRecommendation as $value) {
                                    if (isset($value->Value)) {
                                        $enumRequired[] = $value->Value;
                                    }
                                }                                
                            }
                            $requiredAttributes [$item->Name] = [
                                    'ebaymultiaccount_attribute_name' => $item->Name, 'ebaymultiaccount_attribute_type' => $item->ValidationRules->SelectionMode, 'ebaymultiaccount_attribute_enum' => implode(',', $enumRequired), 'magento_attribute_code' => '', 'required' => 1
                                ];
                        } else {
                            if (isset($item->ValueRecommendation)) {
                                foreach ($item->ValueRecommendation as $value) {
                                    if (isset($value->Value)) {
                                        $enumOptional[] = $value->Value;
                                    }
                                }
                            }
                            $optionalAttribues[$item->Name] = [
                                    'ebaymultiaccount_attribute_name' => $item->Name, 'ebaymultiaccount_attribute_type' => $item->ValidationRules->SelectionMode, 'ebaymultiaccount_attribute_enum' => implode(',', $enumOptional)
                                ];
                        }
                    }
                }                    
            }                
        }
        
        $this->_ebaymultiaccountAttribute[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];
        
        $this->_ebaymultiaccountAttribute[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        ];
        return $this->_ebaymultiaccountAttribute;
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array
     */
    public function getCategoryFeature()
    {
        $catId = $this->getCatId();
        $_ebaymultiaccountAttributeFeature = [];
        if($this->_profile && $this->_profile->getId()>0) {
            $catArray = json_decode($this->_profile->getProfileCategory(), true);
            $data = array_reverse($catArray);
            if ($data) {
                foreach ($data as $value) {
                    if ($value != "") {
                        $catId = $value;
                        break;
                    }
                }
            }
        }
        if ($catId) {
            $limit = ['ConditionEnabled','ConditionValues', 'BestOfferEnabled', 'ISBNEnabled', 'EANEnabled', 'UPCEnabled'];
            $getCatFeatures = $this->helper->getCategoryFeatures($catId, $limit);
            $getCatFeatures = isset($getCatFeatures->Category) ? $getCatFeatures->Category : false;
            if (isset($getCatFeatures->ConditionValues)) {
                $valueForDropdown = $getCatFeatures->ConditionValues->Condition;
                $_ebaymultiaccountAttributeFeature = [];
                if (count($valueForDropdown) > 1) {
                    foreach ($valueForDropdown as $key => $value) {
                        $_ebaymultiaccountAttributeFeature['Condition'][$value->ID] = $value->DisplayName;
                    }
                } else {
                    $_ebaymultiaccountAttributeFeature['Condition'][$valueForDropdown->ID] = $valueForDropdown->DisplayName;
                }
            }
            if (isset($getCatFeatures->BestOfferEnabled) && $getCatFeatures->BestOfferEnabled == 'true') {
                $_ebaymultiaccountAttributeFeature['BestOfferEnabled'] = "Best Offer enabled for this category. mapping available in optional attribute section.";
            }
            if (isset($getCatFeatures->UPCEnabled)) {
                $_ebaymultiaccountAttributeFeature['upc'] = "UPC required (if don't have then please fill 'Does Not Apply' as in default)";
            }
            if (isset($getCatFeatures->ISBNEnabled)) {
                $_ebaymultiaccountAttributeFeature['isbn'] = "ISBN required (if don't have then please fill 'Does Not Apply' as in default)";
            }
            if (isset($getCatFeatures->EANEnabled)) {
                $_ebaymultiaccountAttributeFeature['ean'] = "EAN required (if don't have then please fill 'Does Not Apply' as in default)";
            }
        }
        $this->_ebaymultiaccountAttributeFeature = $_ebaymultiaccountAttributeFeature;
        return $this->_ebaymultiaccountAttributeFeature;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_ebaymultiaccountAttribute[0]['value'];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileCatAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
            else
                $data=[];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getSavedCatFeatures()
    {
        $data = "";
        if ( $this->_profile && $this->_profile->getId() > 0 ) 
            $data = $this->_profile->getProfileCatFeature();
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
