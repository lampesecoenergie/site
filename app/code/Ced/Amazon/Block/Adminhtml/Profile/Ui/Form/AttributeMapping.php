<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Ui\Form;

class AttributeMapping extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = 'Ced_Amazon::profile/mappings/attributes.phtml';

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory  */
    public $attributeFactory;

    /** @var \Magento\Framework\Registry  */
    public $registry;

    /** @var \Ced\Amazon\Model\Profile  */
    public $profile;

    /** @var \Ced\Amazon\Helper\Category  */
    public $category;

    /** @var array */
    public $amazonAttribute;

    /** @var \Magento\Framework\App\Request\Http  */
    public $request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory,
        \Magento\Framework\Registry $registry,
        \Ced\Amazon\Model\Profile $profile,
        \Ced\Amazon\Helper\Category $category,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->category = $category;
        $this->request = $context->getRequest();
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Profile
     * @return \Ced\Amazon\Model\Profile|mixed
     */
    public function getProfile()
    {
        if (!isset($this->profile)) {
            /** @var \Ced\Amazon\Model\Profile profile */
            $this->profile = $this->registry->registry('amazon_profile');
        }

        return $this->profile;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Add Attribute'),
                'onclick' => 'return amazonAttributeControl.addItem()',
                'class' => 'add'
            ]
        );

        $button->setName('amazon_add_attribute_mapping_button');
        return $button->toHtml();
    }

    public function getAmazonAttributes()
    {
        // For AJAX
        $this->amazonAttribute = $this->getAttributes();
        if (isset($this->amazonAttribute) && !empty($this->amazonAttribute)) {
            return $this->amazonAttribute;
        }

        $accountId = $this->getProfile()->getData(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID);
        $categoryId = $this->getProfile()->getData(\Ced\Amazon\Model\Profile::COLUMN_CATEGORY);
        $subCategoryId = $this->getProfile()->getData(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY);
        $marketplaceIds = $this->getProfile()->getMarketplaceIds();
        $requiredAttributes = [];
        $optionalAttributes = [];

        try {
            if (isset($marketplaceIds, $categoryId, $subCategoryId, $accountId) &&
                !empty($marketplaceIds) && !empty($accountId) && is_array($marketplaceIds)) {
                foreach ($marketplaceIds as $marketplaceId) {
                    $params = [
                        'marketplaceId' => $marketplaceId,
                        'minOccurs' => '1'
                    ];

                    $requiredAttributes = array_merge(
                        $requiredAttributes,
                        $this->category->getAttributes($categoryId, $subCategoryId, $params)
                    );

                    $params = [
                        'marketplaceId' => $marketplaceId,
                        'minOccurs' => '0'
                    ];

                    $optionalAttributes = array_merge(
                        $optionalAttributes,
                        $this->category->getAttributes($categoryId, $subCategoryId, $params)
                    );

                    // Adding ASIN alternate field
                    $optionalAttributes["StandardProductID_Value_ASIN"] = [
                        'sequence' => '20\20',
                        'name' => 'ASIN',
                        'dataType' => "Barcode",
                        'minOccurs' => '0',
                        "length" => "8:16"
                    ];

                    // Bullet Points
                    foreach (\Ced\Amazon\Helper\Product::BULLET_POINTS as $i => $bullet) {
                        $optionalAttributes[$bullet] = [
                            'sequence' => '30\4' . ($i+1),
                            'name' => 'BulletPoint ' . ($i+1),
                            'dataType' => "LongStringNotNull",
                            'minOccurs' => '0',
                        ];
                    }

                    // Search Terms
                    $sequence = 161;
                    foreach (\Ced\Amazon\Helper\Product::SEARCH_TERMS as $i => $search) {
                        $optionalAttributes[$search] = [
                            'sequence' => "30\\". ($sequence+$i+1),
                            'name' => 'SearchTerm '. ($i+1),
                            'dataType' => "StringNotNull",
                            'minOccurs' => '0',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            //$this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
        }

        $this->amazonAttribute[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];

        $this->amazonAttribute[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttributes
        ];

        return $this->amazonAttribute;
    }

    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes */
        $attributes = $this->attributeFactory->create();

        $preparedAttributes[''] = '--please select--';
        $preparedAttributes['default_value'] = '[Default Value]';
        /** @var \Magento\Eav\Model\Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            $label = $attribute->getData('frontend_label');
            $code = $attribute->getAttributeCode();
            $preparedAttributes[$attribute->getData('attribute_code')] = $label." [{$code}]";
        }

        return $preparedAttributes;
    }

    public function getMappedAttribute()
    {
        $required = isset($this->amazonAttribute[0]['value']) ? $this->amazonAttribute[0]['value'] : [] ;
        $optional = isset($this->amazonAttribute[1]['value']) ? $this->amazonAttribute[1]['value'] : [] ;
        $optional = array_filter($optional, [$this, 'isMapped']);
        $attributes = array_merge($required, $optional);

        if ($this->getProfile() && $this->getProfile()->getId() > 0) {
            $profileAttributes = $this->getProfile()->getData(\Ced\Amazon\Model\Profile::COLUMN_ATTRIBUTES);
            $attributes = is_array($profileAttributes) ? $this->merge($attributes, $profileAttributes) : [];

            $optional = isset($this->amazonAttribute[1]['value']) ? $this->amazonAttribute[1]['value'] : [] ;
            foreach ($optional as $id => $attribute) {
                if (isset($attribute['name'], $attributes[$id], $attribute['name'])) {
                    $attributes[$id]['name'] = $attribute['name'];
                }
            }
        }

        return $attributes;
    }

    public function isMapped($value)
    {
        $status = false;
        if ((isset($value[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE]) &&
            !empty($value[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE]))
            || (isset($value[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE]) &&
            !empty($value[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE]))
        ) {
            $status = true;
        }

        return $status;
    }

    private function merge(array $a = [], array $b = [])
    {
        $result = array_merge($a, $b);
        foreach ($a as $id => $item) {
            if (isset($result[$id]['minOccurs'], $item['minOccurs'])) {
                $result[$id]['minOccurs'] = $item['minOccurs'];
            }
        }

        return $result;
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

    /**
     * Get options list of an amazon attribute
     * @param array $attribute
     * @return array
     */
    public function getAttributeOptions($attribute = [])
    {
        $options =  isset($attribute['restriction']['optionValues']) &&
        !empty($attribute['restriction']['optionValues']) ? $attribute['restriction']['optionValues'] : [];
        return $options;
    }

    /**
     * TODO: handle all types: int,text. check length.
     * Get attribute type text or select
     * @param array $attribute
     * @return string
     */
    public function getAttributeType($attribute = [])
    {
        $type = !empty($this->getAttributeOptions($attribute)) ? "select" : "text";
        return $type;
    }

    /**
     * Get attribute name
     * @param array $attribute
     * @return mixed|string
     */
    public function getAttributeName($attribute = [])
    {
        $name = isset($attribute['name']) ? $attribute['name'] : "";
        return $name;
    }
}
