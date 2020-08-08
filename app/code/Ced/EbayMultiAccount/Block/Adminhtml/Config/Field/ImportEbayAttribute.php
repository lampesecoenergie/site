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
 * Class GlobalShippingMethodList
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Config\Field
 */
class ImportEbayAttribute extends \Magento\Framework\View\Element\Html\Select
{
    private $_ebayAttribute;

    /**
     * GlobalShippingMethodList constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Ced\EbayMultiAccount\Model\Config\InternationalShippingService $shipMethod
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    protected function _getEbayAttribute()
    {
        if ($this->_ebayAttribute === null) {
            $ebayAttrs = array(
                array(
                    'value' => 'SKU',
                    'label' => 'SKU ( Custom Label )'
                ),
                array(
                    'value' => 'Title',
                    'label' => 'Title'
                )
            );

            $this->_ebayAttribute = $ebayAttrs;
        }
        return $this->_ebayAttribute;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getEbayAttribute() as $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
