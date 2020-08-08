<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\EbayMultiAccount\Model\Product\Attribute\Backend;

use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;

/**
 * Backend model for set of EAV attributes with 'frontend_input' equals 'price'.
 *
 * @api
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class MultiShipping extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Catalog\Model\Attribute\ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
        $this->localeFormat = $localeFormat;
        $this->scopeOverriddenValue = $scopeOverriddenValue
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ScopeOverriddenValue::class);
    }

    /**
     * Process data after load
     *
     * @return void
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);
        if(!is_null($value) /*&& !is_string($value)*/ && ($attributeCode == 'ebay_domestic_shipping' || $attributeCode == 'ebay_inter_shipping_setting')) {
            $value = json_decode($value, true);
        }
        // $value may be passed as null to unset the attribute
        $object->setData($attributeCode, $value, 0);
        return $this;
    }

    /**
     * After Save Price Attribute manipulation
     * Processes product price attributes if price scoped to website and updates data when:
     * * Price changed for non-default store view - will update price for all stores assigned to current website.
     * * Price will be changed according to store currency even if price changed in product with default store id.
     * * In a case when price was removed for non-default store (use default option checked) the default store price
     * * will be used instead
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function beforeSave($object)
    {
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);
        if(!is_null($value) && is_array($value) && ($attributeCode == 'ebay_domestic_shipping' || $attributeCode == 'ebay_inter_shipping_setting')) {
            $value = json_encode($value);
        }
        // $value may be passed as null to unset the attribute
        
        $object->setData($attributeCode, $value);
        return $this;
    }
}
