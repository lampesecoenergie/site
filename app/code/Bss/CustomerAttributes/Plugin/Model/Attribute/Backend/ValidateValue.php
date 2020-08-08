<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend as ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend as DefaultBackend;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime as BackendDatetime;
use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime as FrontendDatetime;

/**
 * Class ValidateValue
 *
 * @package Bss\CustomerAttributes\Plugin\Model\Attribute\Backend
 */
class ValidateValue
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * ValidateValue constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     */
    public function __construct(\Bss\CustomerAttributes\Helper\Customerattribute $customerattribute)
    {
        $this->customerAttribute = $customerattribute;
    }

    /**
     * @param ArrayBackend|DefaultBackend|BackendDatetime|FrontendDatetime $subject
     * @param mixed $proceed
     * @param \Magento\Framework\DataObject $object
     * @return bool
     */
    public function aroundValidate($subject, callable $proceed, $object)
    {
        $attribute = $subject->getAttribute();
        $usedInForms = $attribute->getUsedInForms();

        if (is_array($usedInForms) && in_array('is_customer_attribute', $usedInForms)) {
            if (!in_array('customer_account_edit_frontend', $usedInForms)) {
                return true;
            }
            if (!$this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
                return true;
            }
        }
        return $proceed($object);
    }
}
