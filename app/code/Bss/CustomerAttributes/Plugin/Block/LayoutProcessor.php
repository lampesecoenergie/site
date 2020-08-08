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

namespace Bss\CustomerAttributes\Plugin\Block;

/**
 * Class LayoutProcessor
 * @package Bss\CustomerAttributes\Plugin\Block
 */
class LayoutProcessor
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * LayoutProcessor constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * After Process
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @codingStandardsIgnoreStart
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->helper->getConfig('bss_customer_attribute/general/enable')) {
            return $jsLayout;
        }
        $customerId = $this->getSessionCustomerId();
        $defaultShippingAddress = false;
        if ($customerId != 0) {
            try {
                $defaultShippingAddress = $this->accountManagement->getDefaultBillingAddress($customerId);
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        $quote = $this->checkoutSession->getQuote();
        $elementTmpl = $this->setElementTmpl();
        $types = $this->setTypes();
        $attributeHelper = $this->helper;
        $attributeCollection = $attributeHelper->getUserDefinedAttributes();
        $fieldCount = 0;
        $customerAttributeTitleComponent = [
            'component' => 'Bss_CustomerAttributes/js/view/title',
            "template" => "Bss_CustomerAttributes/title",
            'sortOrder' => 499,
        ];
        $addTitle = false;
        foreach ($attributeCollection as $attribute) {
            if ($customerId != 0) {
                $fieldValue = $attributeHelper->getCustomer($customerId)->getData($attribute->getAttributeCode());
            } else {
                $fieldValue = false;
            }
            if (!$attributeHelper->isAttribureAddtoCheckout($attribute->getAttributeCode())) {
                continue;
            }
            if ($attributeHelper->isHideIfFill($attribute->getAttributeCode()) &&
                $fieldValue &&
                $fieldValue != ''
            ) {
                continue;
            }
            if ($attribute->getFrontendInput() == 'file') {
                continue;
            }
            $label = $attribute->getStoreLabel($attributeHelper->getStoreId());
            $name = $this->setVarName($attribute);
            $validation = $this->setVarValidation($attribute);
            $options = $this->getOptions($attribute);
            $fieldDefaultValue = $attributeHelper->getDefaultValueRequired($attribute);
            $default = $this->setVarDefault($attribute, $fieldValue, $options, $fieldDefaultValue);
            $componentContent = [
                'component' => $types[$attribute->getFrontendInput()],
                'config' => [
                    'template' => 'ui/form/field',
                    'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
                    'id' => $attribute->getAttributeCode()
                ],
                'options' => $options,
                'dataScope' => $name,
                'label' => $label,
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => $validation,
                'sortOrder' => $attribute->getSortOrder() + 500,
                'id' => 'bss_customer_attribute[' . $attribute->getAttributeCode() . ']',
                'default' => $default,
            ];
            if ($attribute->getFrontendInput() !== 'boolean') {
                $componentContent['caption'] = __('Please select');
            }
            if ($quote->getIsVirtual() == 1) {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['afterMethods']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['afterMethods']['children'][$attribute->getAttributeCode()] = $componentContent;
            } elseif ($defaultShippingAddress) {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['before-form']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children'][$attribute->getAttributeCode()] = $componentContent;
            } else {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['shipping-address-fieldset']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][$attribute->getAttributeCode()] = $componentContent;
            }
            $addTitle = true;
            $fieldCount++;
        }
        if ($fieldCount > 0) {
            if ($defaultShippingAddress) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['address-list-additional-addresses']['children']['bss-customer-attributes-validate'] = [
                    'component' => 'Bss_CustomerAttributes/js/view/customer-attributes-validate',
                    'sortOrder' => 900
                ];
            } else {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['bss-customer-attributes-validate'] = [
                    'component' => 'Bss_CustomerAttributes/js/view/customer-attributes-validate',
                    'sortOrder' => 900
                ];
            }
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['beforeMethods']['children']['bss-customer-attributes-validate'] = [
                'component' => 'Bss_CustomerAttributes/js/view/payment-validation',
                'sortOrder' => 900
            ];
        }
        return $jsLayout;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return array
     */
    private function setVarValidation($attribute)
    {
        if ($attribute->getIsRequired() == 1) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $validation['validate-one-required'] = true;
                $validation['required-entry'] = true;
            } else {
                $validation['required-entry'] = true;
            }
        }
        $validation[$attribute->getFrontendClass()] = true;
        if ($attribute->getFrontendInput() == 'date') {
            $validation['validate-date'] = 'M/d/Y';
            $validation['validate-time'] = 'hh:mm';
        }
        return $validation;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param string $fieldValue
     * @param array $options
     * @param mixed $fieldDefaultValue
     * @return array
     */
    private function setVarDefault($attribute, $fieldValue, $options, $fieldDefaultValue)
    {
        $default = [];
        $selectedOptions = [];
        $selectList = ['select', 'boolean', 'multiselect', 'checkboxs'];
        if (!is_array($fieldValue)) {
            $selectedOptions = explode(',', $fieldValue);
        }
        if (in_array($attribute->getFrontendInput(), $selectList)) {
            if ($fieldValue) {
                $optionReBuild = [];
                foreach ($options as $option) {
                    $optionReBuild[] = $option['value'];
                }
                $default = array_intersect($selectedOptions, $optionReBuild);
            } else {
                $default = explode(',', $fieldDefaultValue);
            }
        } else {
            if ($attribute->getFrontendInput() == 'date') {
                if ($fieldValue) {
                    $date = date_create($fieldValue);
                    $date = date_format($date, 'm/d/Y');
                } else {
                    $default = $attribute->getDefaultValue();
                }
            } else {
                if ($fieldValue) {
                    $default = $fieldValue;
                } else {
                    $default = $attribute->getDefaultValue();
                }
            }
        }
        return $default;
    }

    /**
     * Get Options
     *
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOptions($attribute)
    {
        $options = [];
        if ($attribute->getFrontendInput() == 'text' ||
            $attribute->getFrontendInput() == 'textarea' ||
            $attribute->getFrontendInput() == 'file'
        ) {
            return $options;
        }
        if ($attribute->getFrontendInput() == 'date') {
            $options = [
                "dateFormat" => 'M/d/Y',
                "timeFormat" => 'hh:mm'
            ];
        } elseif ($attribute->getFrontendInput() == 'boolean') {
            $options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        } else {
            $optionsList = $this->helper->getAttributeOptions($attribute->getAttributeCode());
            foreach ($optionsList as $option) {
                if ($option['value'] == '') {
                    continue;
                }
                $options[] = ['value' => $option['value'], 'label' => $option['label']];
            }
        }
        return $options;
    }

    /**
     * Set Variable Name
     *
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return string
     */
    private function setVarName($attribute)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $name = 'bss_customer_attributes[' . $attribute->getAttributeCode() . '][]';
        } else {
            $name = 'bss_customer_attributes[' . $attribute->getAttributeCode() . ']';
        }
        return $name;
    }

    /**
     * Set Types
     *
     * @return array
     */
    private function setTypes()
    {
        return [
            'text' => 'Magento_Ui/js/form/element/abstract',
            'textarea' => 'Magento_Ui/js/form/element/textarea',
            'date' => 'Magento_Ui/js/form/element/date',
            'boolean' => 'Magento_Ui/js/form/element/select',
            'select' => 'Magento_Ui/js/form/element/select',
            'radio' => 'Magento_Ui/js/form/element/select',
            'multiselect' => 'Magento_Ui/js/form/element/multiselect',
            'checkboxs' => 'Bss_CustomerAttributes/js/form/element/checkboxes',
            'file' => 'Magento_Ui/js/form/element/file-uploader'
        ];
    }

    /**
     * Set Element Tmpl
     *
     * @return array
     */
    private function setElementTmpl()
    {
        return [
            'text' => 'ui/form/element/input',
            'textarea' => 'ui/form/element/textarea',
            'date' => 'ui/form/element/date',
            'select' => 'ui/form/element/select',
            'boolean' => 'ui/form/element/select',
            'radio' => 'Bss_CustomerAttributes/form/element/radio',
            'multiselect' => 'ui/form/element/multiselect',
            'checkboxs' => 'Bss_CustomerAttributes/form/element/checkboxes',
            'file' => 'ui/form/element/uploader/uploader'
        ];
    }

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    private function getSessionCustomerId()
    {
        if ($this->customerSession->getCustomerId()) {
            return $this->customerSession->getCustomerId();
        }
        return 0;
    }
}
