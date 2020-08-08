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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Eav\Helper\Data;
use Magento\Framework\App\ObjectManager;

/**
 * Class Advanced
 *
 * @package Bss\CustomerAttributes\Block\Adminhtml\Attribute\Edit\Tab
 */
class Advanced extends Generic
{
    /**
     * Eav data
     *
     * @var Data
     */
    protected $eavData = null;

    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var array
     */
    protected $disableScopeChangeList;

    /**
     * @var PropertyLocker
     */
    private $propertyLocker;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
     */
    protected $helperCustomerAttribute;

    /**
     * Advanced constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
     * @param array $disableScopeChangeList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Yesno $yesNo,
        Data $eavData,
        \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute,
        array $disableScopeChangeList = ['sku'],
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->eavData = $eavData;
        $this->helperCustomerAttribute = $helperCustomerAttribute;
        $this->disableScopeChangeList = $disableScopeChangeList;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Advanced Tab
     *
     * @return $this|Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Advanced Attribute Properties'), 'collapsable' => true]
        );
        $yesno = $this->yesNo->toOptionArray();

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code', 'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass
            ]
        );
        $fieldset->addField(
            'max_file_size',
            'text',
            [
                'name' => 'max_file_size', 'label' => __('Maximum File Size (KB)'),
                'title' => __('Maximum File Size (kb)'), 'class' => 'validate-digits'
            ],
            'attribute_code'
        );
        $fieldset->addField(
            'file_extensions',
            'text',
            [
                'name' => 'file_extensions', 'label' => __('File Extensions'),
                'title' => __('File Extensions'), 'note' => __('Comma separated')
            ],
            'max_file_size'
        );
        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name' => 'default_value_text', 'label' => __('Default Value'),
                'title' => __('Default Value'), 'value' => $attributeObject->getDefaultValue()
            ]
        );
        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name' => 'default_value_yesno', 'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $yesno, 'value' => $attributeObject->getDefaultValue()
            ]
        );
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date', 'label' => __('Default Value'),
                'title' => __('Default Value'), 'value' => $attributeObject->getDefaultValue(),
                'date_format' => $dateFormat
            ]
        );
        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => 'default_value_textarea', 'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue()
            ]
        );
        $defaultRequired = $this->helperCustomerAttribute->getDefaultValueRequired($attributeObject);
        $fieldset->addField(
            'default_value_text_required',
            'text',
            [
                'name' => 'default_value_text_required', 'label' => __('Default Value Required For Existing Customer'),
                'title' => __('Default Value Required For Existing Customer'), 'value' => $defaultRequired
            ]
        );
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date_required',
            'date',
            [
                'name' => 'default_value_date_required', 'label' => __('Default Value Required For Existing Customer'),
                'title' => __('Default Value Required For Existing Customer'), 'value' => $defaultRequired,
                'date_format' => $dateFormat
            ]
        );
        $fieldset->addField(
            'default_value_textarea_required',
            'textarea',
            [
                'name' => 'default_value_textarea_required',
                'label' => __('Default Value Required For Existing Customer'),
                'title' => __('Default Value Required For Existing Customer'),
                'value' => $defaultRequired
            ]
        );
        $fieldset->addField(
            'default_value_file_required',
            'file',
            [
                'name' => 'default_value_file_required',
                'label' => __('Default Value Required For Existing Customer'),
                'title' => __('Default Value Required For Existing Customer'),
                'value' => $defaultRequired,
                'note' =>'<a href="'.$this->helperCustomerAttribute->getViewFile($defaultRequired).'">'
                    .$defaultRequired.'</a>'
            ]
        );
        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name' => 'frontend_class', 'label' => __('Input Validation for Customer'),
                'title' => __('Input Validation for Customer'),
                'values' => $this->eavData->getFrontendClasses($attributeObject->getEntityType()->getEntityTypeCode()),
                'note' => __(
                    'It only works at frontend and Input type must be Text Field'
                )
            ]
        );
        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
        }
        $this->setForm($form);
        $this->getPropertyLocker()->lock($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAttributeObject()->getData());
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

    /**
     * Get property locker
     *
     * @return PropertyLocker
     */
    private function getPropertyLocker()
    {
        if (null === $this->propertyLocker) {
            $this->propertyLocker = ObjectManager::getInstance()->get(PropertyLocker::class);
        }
        return $this->propertyLocker;
    }
}
