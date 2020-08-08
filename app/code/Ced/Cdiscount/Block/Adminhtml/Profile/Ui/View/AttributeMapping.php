<?php

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View;

use Ced\Cdiscount\Helper\Config;
use Magento\Store\Model\StoreManagerInterface;

class AttributeMapping extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = 'Ced_Cdiscount::profile/attribute/attributes.phtml';

    public $_objectManager;

    public $_coreRegistry;

    public $profile;

    public $category;

    public $modelName;

    public $_cdiscountAttribute;

    public $request;

    public $json;

    public $magentoAttributes;

    public $storeId;

    public $modelname;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Helper\Profile $profile,
        \Ced\Cdiscount\Helper\Category $category,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->category = $category;
        $this->request = $request;
        $this->magentoAttributes = $attributeCollection;
        $id = $this->request->getParam('id');
        $this->profile = $profile->getProfile(null, $id);
        $this->json = $json;
        $this->storeId = $config->getStore();
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
                'onclick' => 'return cdiscountAttributeControl.addItem()',
                'class' => 'add'
            ]
        );

        $button->setName('cdiscount_add_attribute_mapping_button');
        return $button->toHtml();
    }

    public function getCdiscountAttributes()
    {
        // For AJAX
        $this->_cdiscountAttribute = $this->getAttributes();
        if (isset($this->_cdiscountAttribute) and !empty($this->_cdiscountAttribute)) {
            return $this->_cdiscountAttribute;
        }
        $category = $this->profile->getProfileCategory();
        $optionalAttributes = $this->category->getAttributes('optional');
        if (isset($category) and !empty($category)) {
            $requiredAttributes = $this->category->getAttributes('model', $category);
        } else {
            $requiredAttributes = $this->category->getAttributes('required');
        }
        $this->modelname = $this->category->getModelName();
        $this->_cdiscountAttribute[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];

        $this->_cdiscountAttribute[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttributes
        ];
        return $this->_cdiscountAttribute;
    }

    public function getModelName()
    {
        $modelname = !empty($this->modelName) ? $this->modelName : 'SOUMISSION CREATION PRODUITS_MK';
        return $modelname;
    }

    public function getMagentoAttributes()
    {
        $store = $this->_storeManager->getStore($this->storeId);

        $attributes = $this->magentoAttributes
            ->create()
            ->getItems();
        $magentoAttributes[''] = [
            'name' => "--please select--",
            'code' => "",
            'option_values' => '{}'
        ];
        foreach ($attributes as $attribute) {
            $type = "";
            $optionValues = "{}";
            $attributeOptions = $attribute->setStoreId($store->getId())->getSource()->getAllOptions(false);
            if (!empty($attributeOptions) and is_array($attributeOptions)) {
                $type = " [ select ]";
                foreach ($attributeOptions as &$option) {
                    if (isset($option['label']) and is_object($option['label'])) {
                        $option['label'] = $option['label']->getText();
                    }
                }

                $attributeOptions = str_replace('\'', '&#39;', $this->json->jsonEncode($attributeOptions));
                $optionValues = addslashes($attributeOptions);
            }
            $magentoAttributes[$attribute->getAttributecode()]['code'] = $attribute->getAttributecode();
            $magentoAttributes[$attribute->getAttributecode()]['name'] = is_object($attribute->getStoreLabel($store)) ?
                addslashes($attribute->getStoreLabel($store)->getText() . $type):
                addslashes($attribute->getStoreLabel($store) . $type);
            $magentoAttributes[$attribute->getAttributecode()]['option_values'] = $optionValues;
        }
        return $magentoAttributes;
    }

    public function getMappedAttribute()
    {
        $data = $this->_cdiscountAttribute[0]['value'];
        if ($this->profile && $this->profile->getId() > 0) {
            $requiredAttributes = $this->profile->getRequiredAttributes();
            $optionalAttributes = $this->profile->getOptionalAttributes();
            if (isset($requiredAttributes) and !empty($requiredAttributes)) {
                /*foreach ($requiredAttributes as &$attribute) {
                    $attribute['options'] = [];
                }
                $optionalAttributes = $this->profile->getOptionalAttributes();
                foreach ($optionalAttributes as &$attribute) {
                    $attribute['options'] = [];
                }*/
                $data = array_merge($requiredAttributes, $optionalAttributes);
            }
        }
        return $data;
    }

    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('cdiscount/profile/options');
    }

    public function getProfileId()
    {
        $pid = $this->profile->getId();
        return $pid;
    }
    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render (
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $this->setElement($element);
        return $this->toHtml();
    }
}
