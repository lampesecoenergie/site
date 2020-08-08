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
namespace Bss\CustomerAttributes\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Radio
 *
 * @package Bss\CustomerAttributes\Model\Metadata\Form
 */
class Radio extends \Bss\CustomerAttributes\Model\Metadata\Form\AbstractData
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Radio constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\App\Request\Http $request
     * @param null $value
     * @param null $entityTypeCode
     * @param bool $isAjax
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Request\Http $request,
        $value = null,
        $entityTypeCode = null,
        $isAjax = false
    ) {
        $this->request = $request;
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
    }

    /**
     * {@inheritdoc}
     */
    public function extractValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }

    /**
     * @param array|string $value
     * @return array|bool|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateValue($value)
    {
        $page = $this->request->getFullActionName();
        $errors = [];
        $attribute = $this->getAttribute();
        $label = __($attribute->getStoreLabel());

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
        }
        if ($attribute->getIsRequired() && $page == 'customerattribute_attribute_save') {
            return true;
        }

        if ($attribute->getIsRequired() && empty($value) && $value !== '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if ($this->checkErrors($errors, $attribute, $value)) {
            return true;
        }

        return $errors;
    }

    /**
     * @param array $errors
     * @param Attribute $attribute
     * @param array|string $value
     * @return bool
     */
    protected function checkErrors($errors, $attribute, $value)
    {
        if (!$errors && !$attribute->getIsRequired() && empty($value)) {
            return true;
        }

        if (count($errors) == 0) {
            return true;
        }
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return a text for option value
     *
     * @param string|int $value
     * @return string
     */
    protected function _getOptionText($value)
    {
        foreach ($this->getAttribute()->getOptions() as $option) {
            if ($option->getValue() == $value && !is_bool($value)) {
                return $option->getLabel();
            }
        }
        return '';
    }

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string
     */
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->_value;
        if ($format === ElementFactory::OUTPUT_FORMAT_JSON) {
            $output = $value;
        } elseif ($value != '') {
            $output = $this->_getOptionText($value);
        } else {
            $output = '';
        }

        return $output;
    }
}
