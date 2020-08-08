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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Edit\Tab\Attribute;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Attributes extends \Magento\Backend\Block\Widget implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    public $_template = 'Ced_Amazon::profile/attribute/attributes.phtml';

    public $coreRegistry;

    public $profile;

    public $category;

    public $amazonAttributes;

    public $attributes;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Category $category,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->category = $category;
        $this->attributes = $collectionFactory;
        $this->profile = $this->coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getAmazonAttributes()
    {
        $this->amazonAttributes = $this->getAttributes();
        if (isset($this->amazonAttributes) and !empty($this->amazonAttributes)) {
            return $this->amazonAttributes;
        }

        $categoryId = $this->profile->getProfileCategory();
        $subCategoryId = $this->profile->getProfileSubCategory();
        $requiredAttributes = $this->category->getAttributes($categoryId, $subCategoryId, ['minOccurs' => "1"]);
        $optionalAttributes = $this->category->getAttributes($categoryId, $subCategoryId, ['minOccurs' => "0"]);

        $this->amazonAttributes[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];

        $this->amazonAttributes[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttributes
        ];

        return $this->amazonAttributes;
    }

    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {

        $attributes = $this->attributes->create()->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }

        return $magentoattributeCodeArray;
    }

    public function getMappedAttribute()
    {
        $data = $this->amazonAttributes[0]['value'];
        if ($this->profile && $this->profile->getId() > 0) {
            $requiredAttributes = json_decode($this->profile->getProfileRequiredAttributes(), true);
            $optionalAttributes = json_decode($this->profile->getProfileOptionalAttributes(), true);
            $data = array_merge($requiredAttributes, $optionalAttributes);
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

    public function _prepareLayout()
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

        $button->setName('add_required_item_button');
        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }
}
