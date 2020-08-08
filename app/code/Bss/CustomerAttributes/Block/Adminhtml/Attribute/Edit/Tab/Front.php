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
namespace Bss\CustomerAttributes\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Front
 *
 * @package Bss\CustomerAttributes\Block\Adminhtml\Attribute\Edit\Tab
 */
class Front extends Generic
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var \Bss\CustomerAttributes\Model\Config\Source\EnableDisable
     */
    protected $enableDisable;

    /**
     * @var PropertyLocker
     */
    protected $propertyLocker;
    /**
     * @var \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection
     */
    private $attrSection;

    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $integration;

    /**
     * Front constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param \Bss\CustomerAttributes\Model\Config\Source\EnableDisable $enableDisable
     * @param \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection $attrSection
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $integration
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        \Bss\CustomerAttributes\Model\Config\Source\EnableDisable $enableDisable,
        \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection $attrSection,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $integration,
        PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->propertyLocker = $propertyLocker;
        $this->enableDisable = $enableDisable;
        $this->attrSection = $attrSection;
        $this->integration = $integration;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Front Tab
     *
     * @return Generic
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $yesnoSource = $this->yesNo->toOptionArray();
        $enableDisable = $this->enableDisable->toOptionArray();
        $attributePosOption = $this->attrSection->toOptionArray();
        $fieldset = $form->addFieldset(
            'front_fieldset',
            ['legend' => __('Display Properties'), 'collapsable' => $this->getRequest()->has('popup')]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order', 'label' => __('Sort Order'),
                'title' => __('Sort Order'), 'class' => 'validate-digits',
                'note' => __('The order to display attribute on the frontend'),
            ]
        );
        $fieldset->addField(
            'is_visible',
            'select',
            [
                'name' => 'is_visible', 'label' => __('Status'),
                'title' => __('Status'), 'values' => $enableDisable,
                'value' => '1',
            ]
        );
        $usedInForms = $attributeObject->getUsedInForms();
        $showOnRegistration = $this->checkShowAttribute($attributeObject, $usedInForms, 'customer_account_create_frontend');
        $fieldset->addField(
            'customer_account_create_frontend',
            'select',
            [
                'name' => 'customer_account_create_frontend', 'label' => __('Display in Registration Form'),
                'title' => __('Display in Registration Form'), 'values' => $yesnoSource, 'value' => $showOnRegistration,
            ]
        );
        $showAccountEdit = $this->checkShowAttribute($attributeObject, $usedInForms, 'customer_account_create_frontend');
        $fieldset->addField(
            'customer_account_edit_frontend',
            'select',
            [
                'name' => 'customer_account_edit_frontend', 'label' => __('Display in My Account Page'),
                'title' => __('Display in My Account Page'), 'values' => $yesnoSource, 'value' => $showAccountEdit,
            ]
        );

        if ($this->integration->isB2BRegistrationModuleEnabled()) {
            $showOnBbRegistration = $this->checkShowAttribute($attributeObject, $usedInForms, 'b2b_account_create');
            $fieldset->addField(
                'b2b_account_create',
                'select',
                [
                    'name' => 'b2b_account_create',
                    'label' => __('Display in B2B Registration Form'),
                    'title' => __('Display in B2B Registration Form'),
                    'values' => $yesnoSource,
                    'value' => $showOnBbRegistration,
                ]
            );
            $showOnBbRegistration = $this->checkShowAttribute($attributeObject, $usedInForms, 'b2b_account_edit');
            $fieldset->addField(
                'b2b_account_edit',
                'select',
                [
                    'name' => 'b2b_account_edit',
                    'label' => __('Display in B2B Account page'),
                    'title' => __('Display in B2B Account page'),
                    'values' => $yesnoSource,
                    'value' => $showOnBbRegistration,
                ]
            );
        }

        $showOrderDeltail = $this->checkShowAttribute($attributeObject, $usedInForms, 'order_detail');
        $fieldset->addField(
            'order_detail',
            'select',
            [
                'name' => 'order_detail', 'label' => __('Display in Order Detail Page'),
                'title' => __('Display in Order Detail Page'), 'values' => $yesnoSource, 'value' => $showOrderDeltail,
            ]
        );
        $fieldset->addField(
            'is_used_in_grid',
            'select',
            [
                'name' => 'is_used_in_grid', 'label' => __('Display in Customer Grid'),
                'title' => __('Display in Customer Grid'), 'values' => $yesnoSource,
                'value' => $attributeObject->getIsUsedInGrid(),
            ]
        );
        $showInEmail = $this->checkShowAttribute($attributeObject, $usedInForms, 'show_in_email');
        $fieldset->addField(
            'show_in_email',
            'select',
            [
                'name' => 'show_in_email', 'label' => __('Add to Order Confirmation Email'),
                'title' => __('Add to Order Confirmation Email'), 'values' => $yesnoSource, 'value' => $showInEmail,
            ]
        );
        $showInEmailNewAccount = $this->checkShowAttribute(
            $attributeObject,
            $usedInForms,
            'show_in_email_new_account'
        );
        $fieldset->addField(
            'show_in_email_new_account',
            'select',
            [
                'name' => 'show_in_email_new_account', 'label' => __('Add to New Account Email'),
                'title' => __('Add to New Account Email'), 'values' => $yesnoSource, 'value' => $showInEmailNewAccount,
            ]
        );
        $showOrderFrontend = $this->checkShowAttribute(
            $attributeObject, $usedInForms, 'show_order_frontend'
        );
        $fieldset->addField(
            'show_order_frontend',
            'select',
            [
                'name' => 'show_order_frontend', 'label' => __('Add to Order Frontend'),
                'title' => __('Add to Order Frontend'), 'values' => $yesnoSource, 'value' => $showOrderFrontend,
            ]
        );
        $showCheckoutFrontend = $this->checkShowAttribute(
            $attributeObject, $usedInForms, 'show_checkout_frontend'
        );
        $fieldset->addField(
            'show_checkout_frontend',
            'select',
            [
                'name' => 'show_checkout_frontend', 'label' => __('Display On Checkout page'),
                'title' => __('Display On Checkout page'), 'values' => $yesnoSource, 'value' => $showCheckoutFrontend,
            ]
        );
        $hideIfFill = $this->checkShowAttribute($attributeObject, $usedInForms, 'hide_if_fill_frontend');
        $fieldset->addField(
            'hide_if_fill_frontend',
            'select',
            [
                'name' => 'hide_if_fill_frontend', 'label' => __('Hide If Filled Before'),
                'title' => __('Hide If Filled Before'), 'values' => $yesnoSource, 'value' => $hideIfFill,
            ]
        );
        $showInCustomerAttrSection = $this->checkShowInCustomerAttrSection($attributeObject, $usedInForms);
        $fieldset->addField(
            'show_customer_attr_in',
            'select',
            [
                'name' => 'show_customer_attr_in', 'label' => __('Show attribute in'),
                'title' => __('Show attribute in'),
                'values' => $attributePosOption, 'value' => $showInCustomerAttrSection
            ]
        );
        $this->setForm($form);
        $this->propertyLocker->lock($form);
        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attributeObject
     * @param [] $usedInForms
     * @param string $attributeCode
     * @return int
     */
    private function checkShowAttribute($attributeObject, $usedInForms, $attributeCode)
    {
        if ($attributeObject->getAttributeId()) {
            if (in_array($attributeCode, $usedInForms)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    /**
     * Check if attribute is show in customer attribute section
     *
     * @param \Magento\Customer\Model\Attribute $attributeObject
     * @param array $usedInForms
     * @return string|int
     */
    public function checkShowInCustomerAttrSection($attributeObject, $usedInForms)
    {
        if ($attributeObject->getAttributeId()) {
            if (in_array('signin_infor_section', $usedInForms)) {
                return 'signin_infor_section';
            } elseif (in_array('customer_attr_section', $usedInForms)) {
                return 'customer_attr_section';
            } elseif (in_array('personal_infor_section', $usedInForms)) {
                return 'personal_infor_section';
            } else {
                return 'customer_attr_section';
            }
        } else {
            return 'customer_attr_section';
        }
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $data = $this->getAttributeObject()->getData();
        if (isset($data['sort_order'])) {
            $data['sort_order'] = $data['sort_order'] - \Bss\CustomerAttributes\Helper\Data::DEFAULT_SORT_ORDER;
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
