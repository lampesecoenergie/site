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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Edit\Tab\Attribute;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Attributes extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    public $_template = 'Ced_Cdiscount::profile/attribute/attributes.phtml';


    public $_objectManager;

    public $_coreRegistry;

    public $profile;

    public $category;

    public $_cdiscountAttribute;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Ced\Cdiscount\Helper\Category $category,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->category = $category;

        $this->profile = $this->_coreRegistry->registry('current_profile');

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

    public function getCdiscountAttributes()
    {

        $requiredAttributes = $this->category->getAttributes('required');

        $optionalAttributes = $this->category->getAttributes('optional');

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

    /**
     * Retrieve magento attributes
     *
     * @param  int|null $groupId return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {

        $attributes = $this->_objectManager->create(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection'
        )
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }

        return $magentoattributeCodeArray;
    }

    public function getMappedAttribute()
    {
        $data = $this->_cdiscountAttribute[0]['value'];
        if ($this->profile && $this->profile->getId() > 0) {
            $requiredAttributes = json_decode($this->profile->getProfileRequiredAttributes(), true);
            foreach ($requiredAttributes as &$attribute) {
                $attribute['options'] = json_decode($attribute['options'], true);
            }
            $optionalAttributes = json_decode($this->profile->getProfileOptionalAttributes(), true);
            foreach ($optionalAttributes as &$attribute) {
                $attribute['options'] = json_decode($attribute['options'], true);
            }
            $data = array_merge($requiredAttributes, $optionalAttributes);
        }
        return $data;
    }

    /**
     * Render form element as HTML
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
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

        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }
}
