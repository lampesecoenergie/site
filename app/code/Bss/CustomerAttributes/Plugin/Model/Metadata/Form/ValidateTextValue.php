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
 * Class ValidateTextValue
 *
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class ValidateTextValue
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
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMeta;

    /**
     * ValidateValue constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     * @param \Magento\Framework\App\ProductMetadataInterface $productMeta
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute,
        \Magento\Framework\App\ProductMetadataInterface $productMeta
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerattribute;
        $this->productMeta = $productMeta;
    }

    /**
     * Fix validate for magento 2.3.1
     * @param mixed $subject
     * @param mixed $proceed
     * @param false|string|array|null $value
     * @return bool|mixed
     */
    public function aroundValidateValue($subject, $proceed, $value)
    {
        $page = $this->request->getFullActionName();
        $errors = [];
        $version = $this->productMeta->getVersion();

        if ($version >= "2.3.1") {
            $attribute = $subject->getAttribute();
            $usedInForms = $attribute->getUsedInForms();
            if (in_array('is_customer_attribute', $usedInForms)) {
                if ($this->checkReturnTrue($usedInForms, $page, $attribute)) {
                    return true;
                }
                if ($value === false) {
                    // try to load original value and validate it
                    $value = $subject->getEntity()->getDataUsingMethod($attribute->getAttributeCode());
                }
                if ($attribute->getIsRequired() &&
                    empty($value) && $value !== '0' &&
                    $attribute->getDefaultValue() === null
                ) {
                    $label = __($attribute->getStoreLabel());
                    $errors[] = __('"%1" is a required value.', $label);
                }
            }
        }

        return $this->returnValidate($errors, $proceed($value));
    }

    /**
     * @param array $usedInForms
     * @param string $page
     * @param Attribute $attribute
     * @return bool
     */
    protected function checkReturnTrue($usedInForms, $page, $attribute)
    {
        if ($this->checkUsedForms($usedInForms, $page)) {
            return true;
        }
        if ($attribute->getIsRequired() && $page = 'customerattribute_attribute_save') {
            return true;
        }
        return false;
    }

    /**
     * @param array $usedInForms
     * @return bool
     */
    protected function checkUsedForms($usedInForms, $page)
    {
        if (!$this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
            return true;
        }
        if (!in_array('customer_account_create_frontend', $usedInForms) && $page == 'customer_account_createpost') {
            return true;
        }

        if (!in_array('customer_account_edit_frontend', $usedInForms) && $page == 'customer_account_editPost') {
            return true;
        }
        return false;
    }

    /**
     * @param array $errors
     * @param mixed $proceed
     * @return mixed
     */
    protected function returnValidate($errors, $proceed)
    {
        if (!empty($errors)) {
            return $errors;
        }
        return $proceed;
    }
}
