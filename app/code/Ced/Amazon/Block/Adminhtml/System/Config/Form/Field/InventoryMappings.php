<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 27/7/19
 * Time: 1:25 PM
 */

namespace Ced\Amazon\Block\Adminhtml\System\Config\Form\Field;

class InventoryMappings extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */
    protected $_shippingRegion;

    /**
     * @var
     */
    protected $_shippingMethod;

    protected $_magentoAttr;

    protected $_enabledRenderer;

    protected $enableAttributeRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEnabledRenderer()
    {
        if (!$this->_enabledRenderer) {
            $this->_enabledRenderer = $this->getLayout()->createBlock(
                \Ced\Amazon\Block\Adminhtml\System\Config\Form\Field\Account::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_enabledRenderer->setClass('shipping_region_select');
            $this->_enabledRenderer->setId('<%- _id %>');
        }
        return $this->_enabledRenderer;
    }

    protected function _getEnabledRendererforStore()
    {
        if (!$this->enableAttributeRenderer) {
            $this->enableAttributeRenderer = $this->getLayout()->createBlock(
                \Ced\Amazon\Block\Adminhtml\System\Config\Form\Field\Attributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->enableAttributeRenderer->setClass('attribute_select');
            $this->enableAttributeRenderer->setId('<%- _id %>');
        }
        return $this->enableAttributeRenderer;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {

        $this->addColumn(
            'account',
            [
                'label' => __('Account'),
                'renderer' => $this->_getEnabledRenderer()
            ]
        );
        $this->addColumn(
            'attribute',
            ['label' => __('Magento Attribute'), 'renderer' => $this->_getEnabledRendererforStore()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Attribute');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getEnabledRenderer()->calcOptionHash($row->getData('account'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getEnabledRendererforStore()->calcOptionHash($row->getData('attribute'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
