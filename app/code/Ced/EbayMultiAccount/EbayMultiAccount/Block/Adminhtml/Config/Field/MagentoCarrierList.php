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
 * Class MagentoCarrierList
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Config\Field
 */
class MagentoCarrierList extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var
     */
    private $_magentoCarrier;
    /**
     * @var \Magento\Shipping\Model\Config
     */
    public $_mCarrier;

    /**
     * MagentoCarrierList constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Shipping\Model\Config $mCarrier
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Shipping\Model\Config $mCarrier,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_mCarrier = $mCarrier;
    }

    /**
     * @return array
     */
    protected function _getMagentoCarrier()
    {
        if ($this->_magentoCarrier === null) {
            $carriers = $this->_mCarrier->getAllCarriers();
            foreach ($carriers as $carrierCode => $carrierModel) {
                $options = ['value' => $carrierCode, 'label' => $carrierCode];
                $carriers[] = $options;
            }
            $this->_magentoCarrier = $carriers;
        }
        return $this->_magentoCarrier;
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
            foreach ($this->_getMagentoCarrier() as $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
