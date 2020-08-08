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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Helper;

/**
 * Class B2BRegistrationIntegrationHelper
 * @package Bss\CustomerAttributes\Helper
 */
class B2BRegistrationIntegrationHelper extends \Bss\CustomerAttributes\Helper\Customerattribute
{
    /**
     * @return bool
     */
    public function isB2BRegistrationModuleEnabled()
    {
        return $this->isModuleOutputEnabled('Bss_B2bRegistration');
    }

    /**
     * @param $statusCustomer
     * @param $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkAttrForEditPage($statusCustomer, $attributeCode)
    {
        if (!$statusCustomer || $statusCustomer == '0') {
            return $this->isAttributeForNormalAccountEdit($attributeCode);
        } elseif (isset($statusCustomer) && $statusCustomer != '0') {
            return $this->isAttributeForB2bAccountEdit($attributeCode);
        }
        return false;
    }

    /**
     * @param $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttributeForNormalAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('customer_account_edit_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * @param $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttributeForB2bAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('b2b_account_edit', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in account create
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForCustomerAccountCreate($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('b2b_account_create', $usedInForms)) {
            return true;
        }
        return false;
    }
}
