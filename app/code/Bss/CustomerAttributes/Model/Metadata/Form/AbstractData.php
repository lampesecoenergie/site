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

use Magento\Framework\App\RequestInterface;

// @codingStandardsIgnoreFile
/**
 * EAV Attribute Abstract Data Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractData extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface
     */
    protected $_attribute;

    /**
     * @var string|int|bool
     */
    protected $_value;

    /**
     * @var  string
     */
    protected $_entityTypeCode;

    /**
     * Is AJAX request flag
     *
     * @var boolean
     */
    protected $_isAjax = false;

    /**
     * AbstractData constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param null $value
     * @param null $entityTypeCode
     * @param bool $isAjax
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value = null,
        $entityTypeCode = null,
        $isAjax = false
    ) {
        parent::__construct($localeDate, $logger, $localeResolver);
        $this->_attribute = $attribute;
        $this->_value = $value;
        $this->_entityTypeCode = $entityTypeCode;
        $this->_isAjax = $isAjax;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function extractValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateValue($value)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function compactValue($value)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restoreValue($value)
    {
    }

    /**
     * Return a text for option value
     *
     * @param string|int $value
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getOptionText($value)
    {
    }

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
    }
}
