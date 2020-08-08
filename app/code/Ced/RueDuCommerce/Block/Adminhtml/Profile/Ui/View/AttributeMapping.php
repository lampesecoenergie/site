<?php

namespace Ced\RueDuCommerce\Block\Adminhtml\Profile\Ui\View;

class AttributeMapping extends \Magento\Backend\Block\Template
{
     /**
     * @var string
     */
    public $_template = 'Ced_RueDuCommerce::profile/attribute/attributes.phtml';


    public $_objectManager;

    public $_coreRegistry;

    public $profile;

    public $category;

    public $_rueducommerceAttribute;

    public $request;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Ced\RueDuCommerce\Model\Profile $profile,
        \Ced\RueDuCommerce\Helper\Category $category,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->category = $category;
        $this->request = $request;
        $this->session =  $context->getSession();
        $id = $this->request->getParam('current_profile_id');
        $this->profile = $profile->load($id);
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Attribute'),
                'onclick' => 'return rueducommerceAttributeControl.addItem()',
                'class' => 'add'
            ]
        );

        $button->setName('rueducommerce_add_attribute_mapping_button');
        return $button->toHtml();
    }

    public function getRueDuCommerceAttributes()
    {
        // For AJAX
        $this->_rueducommerceAttribute = $this->getAttributes();

        
        if (isset($this->_rueducommerceAttribute) and !empty($this->_rueducommerceAttribute)) {
            return $this->_rueducommerceAttribute;
        }

        // For Profile Saved
        $categoryId = $this->profile->getProfileCategory();
        $params = [
            'hierarchy' => '',
            'isMandatory' => 1
        ];
        $requiredAttributes = $this->category->getAttributes($params);

        $params = [
            'hierarchy' => '',
            'isMandatory' => 0
        ];
        $optionalAttributes = $this->category->getAttributes($params);


        $this->_rueducommerceAttribute[] = array(
            'label' => 'Required Attributes',
            'value' => $requiredAttributes
        );


        $this->_rueducommerceAttribute[] = array(
            'label' => 'Optional Attributes',
            'value' => $optionalAttributes
        );
        return $this->_rueducommerceAttribute;
    }


    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {

        $attributes = $this->_objectManager->create(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection'
        )
            ->getItems();

        $mattributecode = '--Please Select--';
        /*$magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = '--Default Value--';*/
        $magentoattributeCodeArray[''] =
            [
                'attribute_code' => $mattributecode,
                'attribute_type' => '',
                'input_type' => '',
                'option_values' => ''
            ];
        $magentoattributeCodeArray['default'] =
            [
                'attribute_code' =>"-- Set Default Value --",
                'attribute_type' => '',
                'input_type' => '',
                'option_values' => ''
            ];
        $magentoattributeCodeArray['entity_id'] =
            [
                'attribute_code' =>"Product Id",
                'attribute_type' => '',
                'input_type' => '',
                'option_values' => ''
            ];
        foreach ($attributes as $attribute) {
            $type = "";
            $optionValues = "";
            $attributeOptions = $attribute->getSource()->getAllOptions(false);
            if (!empty($attributeOptions) and is_array($attributeOptions)) {
                $type = " [ select ]";
                foreach ($attributeOptions as &$option) {
                    if (isset($option['label']) and is_object($option['label'])) {
                        $option['label'] = $option['label']->getText();
                    }
                }
                $attributeOptions = str_replace('\'', '&#39;', json_encode($attributeOptions));
                $optionValues = addslashes($attributeOptions);
            }

            if($attribute->getFrontendInput() =='select') {
                $magentoattributeCodeArray[$attribute->getAttributecode()] =
                    [
                        'attribute_code' => $attribute->getFrontendLabel() . $type,
                        'attribute_type' => $attribute->getFrontendInput(),
                        'input_type' => 'select',
                        'option_values' => $optionValues,
                    ];
            } else {
                $magentoattributeCodeArray[$attribute->getAttributecode()] =
                    [
                        'attribute_code' => $attribute->getFrontendLabel(),
                        'attribute_type' => $attribute->getFrontendInput(),
                        'input_type' => '',
                        'option_values' => $optionValues,
                    ];
            }
            //$magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }

        return $magentoattributeCodeArray;
    }

    public function getMappedAttribute()
    {
        $data = $this->_rueducommerceAttribute[0]['value'];
        $reqAttrCodes = array_keys($data);
        $optData = $this->_rueducommerceAttribute[1]['value'];
        $optAttrCodes = array_keys($optData);
        $requiredAttributes = [];
        $optionalAttributes = [];
        if ($this->profile && $this->profile->getId()) {
            $requiredAttributes = json_decode($this->profile->getProfileRequiredAttributes(), true);
            if(is_array($requiredAttributes) && count($requiredAttributes)){
                foreach ($requiredAttributes as &$attribute) {
                    $attribute['options'] = json_decode($attribute['options'],true);
                    if(!in_array($attribute['name'], $reqAttrCodes)) {
                        unset($requiredAttributes[$attribute['name']]);
                    }
                }
            }

            $optionalAttributes = json_decode($this->profile->getProfileOptionalAttributes(), true);
            if(is_array($optionalAttributes) && count($optionalAttributes)){
                foreach ($optionalAttributes as &$attribute) {
                    $attribute['options'] = json_decode($attribute['options'],true);
                    if(!in_array($attribute['name'], $optAttrCodes)) {
                        unset($optionalAttributes[$attribute['name']]);
                    }
                }
            }
            if(is_array($requiredAttributes) && is_array($optionalAttributes)){
            $data = $requiredAttributes + $optionalAttributes + $data;
            }
        }
        return $data;
    }

    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
