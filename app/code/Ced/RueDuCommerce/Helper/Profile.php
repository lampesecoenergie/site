<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Profile For RueDuCommerce Profiling
 *
 * @package Ced\RueDuCommerce\Helper
 */
class Profile extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REQUIRED_ATTRIBUTES = [
        "title",
        "short-desc",
        "standard-price",
        "brand",
        "shipping-length",
        "shipping-width",
        "shipping-height",
        "shipping-weight",
        "offer-condition/condition",
        "item-id",
        "image-url",
        "standard-price",
        "upc",
        "model-number",
        "long-desc",
    ];

    const OPTIONAL_ATTRIBUTES = [
        "mature-content",
        "local-marketplace-flags/is-restricted",
        "local-marketplace-flags/perishable",
        "local-marketplace-flags/requires-refrigeration",
        "local-marketplace-flags/requires-freezing",
        "local-marketplace-flags/contains-alcohol",
        "local-marketplace-flags/contains-tobacco",
        "no-warranty-available"
    ];

    const CONF_REQUIRED_ATTRIBUTES = [
        "title",
        "brand",
        "short-desc",
        "long-desc",
        "model-number"
    ];

    const DEFAULT_ATTRIBUTES = [
        'shipping-length', //set 1.0
        'shipping-width', //set 1.0
        'shipping-height', //set 1.0
        'offer-condition/condition' //set NEW
    ];

    /*
     * Active Profile
     */
    public $profile;

    /*
    * Profile Code
    */
    public $profileCode;

    /**
     * @var $categories
     */
    public $categories;

    /**
     * RueDuCommerce Attributes
     */
    public $attributes;

    /**
     * @var array
     */
    public $rueducommerceAttribute = [];

    /**
     * Json Parser
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    public $requiredAttributes = [];
    public $optionalAttributes = [];
    public $variantAttributes = [];
    public $profileProduct;
    public $profileFactory;
    public $rueducommerceCache;

    /**
     * Profile constructor.
     *
     * @param Context                             $context
     * @param \Magento\Framework\Json\Helper\Data $json
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Catalog\Model\Product $profileProduct,
        \Ced\RueDuCommerce\Model\ProfileFactory $profile,
        \Ced\RueDuCommerce\Helper\Cache $cache
    ) {
        $this->json = $json;
        $this->profileProduct = $profileProduct;
        $this->rueducommerceCache = $cache;
        $this->profileFactory = $profile;
        parent::__construct($context);
    }

    /**
     * Get Profile
     *
     * @param  null|string $productId
     * @param  null|string $profileId
     * @return \Ced\RueDuCommerce\Helper\Profile|null
     */
    public function getProfile($productId = null, $profileId = null)
    {
        if (empty($profileId) or !is_numeric($profileId)) {
            $profileId = $this->profileProduct->load($productId, 'product_id')
                ->getRueducommerceProfileId();
        }

        $this->profile = $this->rueducommerceCache->getValue(\Ced\RueDuCommerce\Helper\Cache::PROFILE_CACHE_KEY . $profileId);

        //if (!isset($this->profile, $this->profile['id']) or empty($this->profile)) {
            $this->setProfile($profileId);
        //}

        return $this;
    }

    /**
     * Set Profile
     *
     * @param  string $profileId
     * @return boolean
     */
    public function setProfile($profileId = null)
    {
        if (isset($profileId)) {
            $this->profile  = $this->profileFactory->create()->load($profileId)->getData();
            if (isset($this->profile) and is_array($this->profile)) {
                if(isset($this->profile['profile_categories']) && $this->profile['profile_categories'])
                $this->profile['profile_categories'] = $this->json->jsonDecode($this->profile['profile_categories']);
                else 
                $this->profile['profile_categories'] =[];

                if(isset($this->profile['profile_required_attributes']) && $this->profile['profile_required_attributes'])
                $requiredAttributes = $this->json->jsonDecode($this->profile['profile_required_attributes']);
                else
                    $requiredAttributes =[];
                foreach ($requiredAttributes as &$attribute) {
                    $validOptionsModified = $optionsModified = [];
                     try {
                            $options = $this->json->jsonDecode($attribute['option_mapping']);
                            $validOptions = $this->json->jsonDecode($attribute['options']);
                        } catch(\Zend_Json_Exception $e) {
                            $options = [];
                            $validOptions = [];
                        }
                    if(count( $options)){
                        foreach ($options as $optionName => $optionValue) {
                            $optionsModified[$optionValue] = $optionName;
                        }
                    }
                    if(count( $validOptions) > 0){
                        foreach ($validOptions as $optionName => $optionValue) {
                            $validOptionsModified[$optionName] = $optionValue;
                        }
                    }
                    $attribute['options'] = $validOptionsModified ;
                    $attribute['option_mapping'] = $optionsModified ;
                }
                $this->profile['profile_required_attributes'] =  $requiredAttributes;
                //$this->json->jsonEncode($requiredAttributes);

                if(isset($this->profile['profile_optional_attributes']) && $this->profile['profile_optional_attributes'])
                $optionalAttributes = $this->json->jsonDecode($this->profile['profile_optional_attributes']);
                else 
                    $optionalAttributes =[];

                foreach ($optionalAttributes as &$attribute) {
                    $validOptionsModified = $optionsModified = [];
                    if(isset($attribute['option_mapping']) && $attribute['option_mapping']) {
                        try {
                            $options = $this->json->jsonDecode($attribute['option_mapping']);
                            $validOptions = $this->json->jsonDecode($attribute['options']);
                        } catch(\Zend_Json_Exception $e) {
                            $options = [];
                            $validOptions = [];
                        }
                    }
                    else 
                        $options =[];
                    if(count( $options)){
                        foreach ($options as $optionName => $optionValue) {
                            $optionsModified[$optionValue] = $optionName;
                        }
                    }
                    if(count( $validOptions) > 0){
                        foreach ($validOptions as $optionName => $optionValue) {
                            $validOptionsModified[$optionName] = $optionValue;
                        }
                    }
                    $attribute['options'] = $validOptionsModified ;
                    $attribute['option_mapping'] = $optionsModified;
                }

                $this->profile['profile_optional_attributes'] =  $optionalAttributes;

                //$attributes = array_merge($requiredAttributes, $optionalAttributes);
                $attributes = $requiredAttributes + $optionalAttributes;
                $this->profile['profile_attributes'] = $attributes;

                $this->rueducommerceCache->setValue(\Ced\RueDuCommerce\Helper\Cache::PROFILE_CACHE_KEY . $profileId, $this->profile);
                return true;
            }
        }
        return false;
    }

    /**
     * Get a Profile Attribute
     *
     * @return array
     */
    public function getAttribute($attributeId)
    {
        if (isset($this->profile['profile_attributes'][$attributeId])) {
            return $this->profile['profile_attributes'][$attributeId];
        }
        return [];
    }

    /**
     * Get a Mapped magento Profile Attribute
     *
     * @return string|boolean
     */
    public function getMappedAttribute($attributeId)
    {
        if (isset($this->profile['profile_attributes'][$attributeId]['magento_attribute_code'])) {
            return $this->profile['profile_attributes'][$attributeId]['magento_attribute_code'];
        }
        return false;
    }

    /**
     * Get Profile Attributes
     *
     * @return array
     */
    public function getAttributes($type = null)
    {

        if (isset($this->profile['profile_attributes'])) {
           
            return $this->profile['profile_attributes'];
        }
        return [];
    }

    /**
     * Get Profile Attributes
     *
     * @return array
     */
    public function getRequiredAttributes($type = null)
    {
        if (isset($this->profile['profile_required_attributes'])) {
            if (isset($type) and !empty($type)) {
                $attributes = [];
                foreach ($this->profile['profile_required_attributes'] as $id => $attribute) {
                    if ($attribute['attributeType'] == $type) {
                        $attributes[$id] = $attribute;
                    }
                }
                return $attributes;
            }
            return $this->profile['profile_required_attributes'];
        }
        return [];
    }

    /**
     * Get Profile Category
     *
     * @return string
     */
    public function getProfileCategory()
    {
        if (isset($this->profile['profile_category'])) {
            return $this->profile['profile_category'];
        }
        return '';
    }

    /**
     * Get Profile Category
     *
     * @return boolean|string
     */
    public function getId()
    {
        if (isset($this->profile['id'])) {
            return $this->profile['id'];
        }
        return false;
    }

    /**
     * Get Profile
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->profile)) {
            return $this->profile;
        }
        return [];
    }

     /**
      * Get All Products Ids
      *
      * @param  null $productId
      * @return mixed
      */
    public function getProducts($productId = null)
    {
        $productIds = [];
        if (isset($productId)) {
            $profile = $this->getProfile(null, $productId);
            $profileId = $profile->getId();
            if (isset($profileId) and !empty($profileId)) {
                $productIds = $this->profileProduct->create()->getCollection()
                    ->addFieldToFilter('profile_id', ['eq' => $profileId])->getColumnValues('product_id');
            }
        } else {
            $productIds = $this->profileProduct->create()->getCollection()->getColumnValues('product_id');
        }
        return $productIds;
    }
}
