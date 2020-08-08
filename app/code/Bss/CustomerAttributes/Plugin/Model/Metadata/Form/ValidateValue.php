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
namespace Bss\CustomerAttributes\Plugin\Model\Metadata\Form;

/**
 * Class ValidateValue
 *
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class ValidateValue
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * ValidateValue constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerattribute;
    }

    /**
     * @param mixed $subject
     * @param bool $result
     * @return bool
     */
    public function afterValidateValue($subject, $result)
    {
        $page=$this->request->getFullActionName();
        $attribute = $subject->getAttribute();
        $usedInForms = $attribute->getUsedInForms();
        if (in_array('is_customer_attribute', $usedInForms)) {
            if (!$this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
                return true;
            }
            if (!in_array('customer_account_create_frontend', $usedInForms) && $page == 'customer_account_createpost') {
                return true;
            }

            if (!in_array('customer_account_edit_frontend', $usedInForms) && $page == 'customer_account_editPost') {
                return true;
            }
            if ($attribute->getData('is_required') && $page = 'customerattribute_attribute_save') {
                return true;
            }
        }
        return $result;
    }
}
