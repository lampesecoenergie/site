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
namespace Bss\CustomerAttributes\Helper;

/**
 * Class Data
 *
 * @package Bss\CustomerAttributes\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_SORT_ORDER = 140;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
    }

    /**
     * Return information array of customer attribute input types
     *
     * @param string $inputType
     * @return array
     */
    public function getAttributeInputTypes($inputType = null)
    {
        $inputTypes = [
            'file' => [
                'validate_types' => ['max_file_size', 'file_extensions','default_value_required']
            ],
            'multiselect' => [
                'validate_types' => [],
                'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
            ],
            'radio' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'data_model' => \Bss\CustomerAttributes\Model\Metadata\Form\Radio::class
            ],
            'checkboxs' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'data_model' => \Bss\CustomerAttributes\Model\Metadata\Form\CheckBoxs::class
            ],
            'boolean' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
            ],
            'date' => [
                'label' => __('Date'),
                'manage_options' => false,
                'validate_types' => ['default_value_required'],
                'validate_filters' => ['date'],
                'filter_types' => ['date'],
                'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                'backend_type' => 'datetime',
                'default_value' => 'date',
            ],
            'text' => [
                'validate_types' => ['default_value_required']
            ],
            'yesno' => [
                'validate_types' => ['default_value_required']
            ],
            'textarea' => [
                'validate_types' => ['default_value_required']
            ],
        ];

        if ($inputType === null) {
            return $inputTypes;
        } else {
            if (isset($inputTypes[$inputType])) {
                return $inputTypes[$inputType];
            }
        }
        return [];
    }

    /**
     * Return default attribute backend model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();

        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    /**
     * Return default attribute source model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeSourceModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    /**
     * Return default attribute data model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeDataModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['data_model'])) {
            return $inputTypes[$inputType]['data_model'];
        }
        return null;
    }

    /**
     * Return Validate Rules by input type
     *
     * @param string $inputType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function getAttributeValidateRules($inputType, array $data)
    {
        $inputTypes = $this->getAttributeInputTypes();
        $rules = [];
        if (isset($inputTypes[$inputType])) {
            foreach ($inputTypes[$inputType]['validate_types'] as $validateType) {
                if (!empty($data[$validateType])) {
                    $rules[$validateType] = $data[$validateType];
                } elseif (!empty($data['scope_' . $validateType])) {
                    $rules[$validateType] = $data['scope_' . $validateType];
                }
            }

            if ($inputType === 'date') {
                $rules['input_validation'] = 'date';
            }
        }

        return $rules;
    }

    /**
     * Get Backend Input Type
     *
     * @param string $type
     * @return null|string
     */
    public function getBackendTypeByInput($type)
    {
        $field = null;
        if ($this->returnVarChar($type)) {
            $field = 'varchar';
            return $field;
        } elseif ($type == 'textarea') {
            $field = 'text';
            return $field;
        } elseif ($type == 'date') {
            $field = 'datetime';
            return $field;
        } elseif ($type == 'select' || $type == 'radio' || $type == 'boolean') {
            $field = 'int';
            return $field;
        } else {
            return $field;
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    private function returnVarChar($type)
    {
        if ($type == 'text' || $type == 'multiselect' || $type == 'checkboxs' || $type == 'file') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Default Value by Input Type
     *
     * @param string $type
     * @param string $required
     * @return string|null
     */
    public function getDefaultValueByInput($type, $required = null)
    {
        $field = '';
        $arrTypeSelect = ['select','radio'];
        $arrDefault = ['text','textarea','boolean','file', 'date'];
        $arrMulti = ['multiselect','checkboxs'];
        if (in_array($type, $arrTypeSelect)) {
            return $field;
        } elseif (in_array($type, $arrDefault)) {
            if ($type == 'boolean') {
                return 'default_value_yesno' . $required;
            } else {
                return 'default_value_'.$type.$required;
            }
        } elseif (in_array($type, $arrMulti)) {
            return null;
        }
        return $field;
    }

    /**
     * Get Config
     *
     * @param string $path
     * @param int $store
     * @param string $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Check is allow set default required attribute for existing customer
     *
     * @return string
     */
    public function isAllowSetDefaultConfig()
    {
        return $this->getConfig('bss_customer_attribute/general/set_required_attribute');
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    public function returnCustomerFactory()
    {
        return $this->customerFactory;
    }
}
