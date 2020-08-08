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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Config\Field;

/**
 * Class GlobalShippingMethods
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Config\Field
 */
class ImportFieldMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */

    protected $_ebayAttrRender;
    /**
     * @var
     */

    protected $_magentoAttrRender;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getMagentoAttrRender()
    {
        if (!$this->_magentoAttrRender) {
            $this->_magentoAttrRender = $this->getLayout()->createBlock(
                'Ced\EbayMultiAccount\Block\Adminhtml\Config\Field\ImportMagentoAttribute',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magentoAttrRender->setClass('magento_attribute');
            $this->_magentoAttrRender->setId('<%- _id %>');
        }
        return $this->_magentoAttrRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getEbayAttrRenderer()
    {
        if (!$this->_ebayAttrRender) {
            $this->_ebayAttrRender = $this->getLayout()->createBlock(
                'Ced\EbayMultiAccount\Block\Adminhtml\Config\Field\ImportEbayAttribute',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_ebayAttrRender->setClass('ebay_attribute');
            $this->_ebayAttrRender->setId('<%- _id %>');
        }
        return $this->_ebayAttrRender;
    }

    protected function _prepareToRender()
    {
        $this->addColumn(
            'ebay_attribute',
            [
                'label' => __('Ebay Attribute'),
                'renderer' => $this->_getEbayAttrRenderer()
            ]
        );
        $this->addColumn(
            'magento_attribute',
            [
                'label' => __('Magento Attribute'),
                'renderer' => $this->_getMagentoAttrRender()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Method');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getEbayAttrRenderer()->calcOptionHash($row->getData('ebay_attribute'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getMagentoAttrRender()->calcOptionHash($row->getData('magento_attribute'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

}