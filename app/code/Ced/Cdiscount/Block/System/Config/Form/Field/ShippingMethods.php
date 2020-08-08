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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Block\System\Config\Form\Field;

class ShippingMethods extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
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

    protected  $_enabledRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEnabledRenderer()
    {
        if (!$this->_enabledRenderer) {
            $this->_enabledRenderer = $this->getLayout()->createBlock(
                'Ced\Cdiscount\Block\System\Config\Form\Field\ShippingMethodList',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_enabledRenderer->setClass('shipping_region_select');
            $this->_enabledRenderer->setId('<%- _id %>');
        }
        return $this->_enabledRenderer;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {

        $this->addColumn(
            'shipping_method',
            [
                'label' => __('Shipping Methods'),
                'renderer' => $this->_getEnabledRenderer()
            ]
        );
        $this->addColumn('price', ['label' => __('Shipping Charges')]);
        $this->addColumn('additional_price', ['label' => __('Additional Shipping Charges	')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Method');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getEnabledRenderer()->calcOptionHash($row->getData('shipping_method'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
