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
 * Class CarrierMapping
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Config\Field
 */
class CarrierMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */
    protected $_shippingRegion;

    /**
     * @var
     */
    protected $_shippingMethod;
    /**
     * @var
     */

    protected $_magentoAttr;
    /**
     * @var
     */
    protected $_enabledRenderer;
    /**
     * @var
     */
    protected $_ebaymultiaccountCarrierRenderer;
    /**
     * @var
     */
    protected $_magentoCarrierRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getEbayMultiAccountCarrierRenderer()
    {
        if (!$this->_ebaymultiaccountCarrierRenderer) {
            $this->_ebaymultiaccountCarrierRenderer = $this->getLayout()->createBlock(
                'Ced\EbayMultiAccount\Block\Adminhtml\Config\Field\CarrierMappingList',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_ebaymultiaccountCarrierRenderer->setClass('ebaymultiaccount_carrier_select');
            $this->_ebaymultiaccountCarrierRenderer->setId('<%- _id %>');
        }
        return $this->_ebaymultiaccountCarrierRenderer;
    }


    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getMagentoCarrierRenderer()
    {
        if (!$this->_magentoCarrierRenderer) {
            $this->_magentoCarrierRenderer = $this->getLayout()->createBlock(
                'Ced\EbayMultiAccount\Block\Adminhtml\Config\Field\MagentoCarrierList',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magentoCarrierRenderer->setClass('magento_carrier_select');
            $this->_magentoCarrierRenderer->setId('<%- _id %>');
        }
        return $this->_magentoCarrierRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn(
            'magento_carrier', array(
                'label' => __('Magento Carrier'),
                'renderer' => $this->_getMagentoCarrierRenderer(),
            )
        );
        $this->addColumn(
            'ebaymultiaccount_carrier', array(
                'label' => __('eBay Carrier'),
                'renderer' => $this->_getEbayMultiAccountCarrierRenderer(),
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Carrier');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getMagentoCarrierRenderer()->calcOptionHash($row->getData('magento_carrier'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getEbayMultiAccountCarrierRenderer()->calcOptionHash($row->getData('ebaymultiaccount_carrier'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}