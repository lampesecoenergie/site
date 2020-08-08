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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * @deprecated
 * Class Profile For Amazon Profiling
 * @package Ced\Amazon\Helper
 */
class Profile extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_CODE_PROFILE_ID = 'amazon_profile_id';
    
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    
    public $storeManager;
    
    /*
     * Active Profile
     */
    public $profile;

    /*
    * Profile Code
    */
    public $profileCode;
    
    /**
     * @todo: remove table dependency
     * @var \Ced\Amazon\Model\ProfileproductsFactory
     */
    public $profileProducts;

    /**
     * @var \Ced\Amazon\Model\ProfileFactory
     */
    public $profileFactory;

    /**
     * @var \Ced\Amazon\Helper\Cache
     */
    public $cache;

    /**
     * Profile constructor.
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Ced\Amazon\Model\ProfileproductsFactory $profileProducts
     * @param \Ced\Amazon\Model\ProfileFactory $profile
     * @param \Ced\Amazon\Helper\Cache $cache
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Amazon\Model\ProfileproductsFactory $profileProducts,
        \Ced\Amazon\Model\ProfileFactory $profile,
        \Ced\Amazon\Helper\Cache $cache
    ) {
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->profileProducts = $profileProducts;
        $this->cache = $cache;
        $this->profileFactory = $profile;
        parent::__construct($context);
    }

    /**
     * Get Profile
     * @param null $productId
     * @param null $profileId
     * @return $this
     * @throws \Exception
     */
    public function getProfile($productId = null, $profileId = null)
    {
        if (empty($profileId) or !is_numeric($profileId)) {
            $profileId = $this->profileProducts->create()
                ->load($productId, 'product_id')
                ->getProfileId();
        }

        $this->profile = $this->cache->getValue(\Ced\Amazon\Helper\Cache::PROFILE_CACHE_KEY . $profileId);

        if (!isset($this->profile, $this->profile['id']) or empty($this->profile)) {
            $this->setProfile($profileId);
        }

        return $this;
    }

    /**
     * Set Profile
     * @param null $profileId
     * @return bool
     * @throws \Exception
     */
    public function setProfile($profileId = null)
    {
        if (isset($profileId)) {
            $this->profile  = $this->profileFactory->create()->load($profileId)->getData();
            if (isset($this->profile, $this->profile['profile_required_attributes'], $this->profile['profile_optional_attributes']) and !empty($this->profile)) {
                $requiredAttributes = $this->json->jsonDecode($this->profile['profile_required_attributes']);
                $optionalAttributes = $this->json->jsonDecode($this->profile['profile_optional_attributes']);
                $attributes = array_merge($requiredAttributes, $optionalAttributes);
                $this->profile['profile_attributes'] = $attributes;
                $this->cache->setValue(\Ced\Amazon\Helper\Cache::PROFILE_CACHE_KEY . $profileId, $this->profile);
                return true;
            }
        }
        return false;
    }

    /**
     * Get a Profile Attribute
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
     * Get Profile Attributes
     * @return array
     */
    public function getAttributes()
    {
        if (isset($this->profile['profile_attributes'])) {
            return $this->profile['profile_attributes'];
        }
        return [];
    }

    /**
     * Get Profile Category
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
     * Get Profile Sub Category
     * @return string
     */
    public function getProfileSubCategory()
    {
        if (isset($this->profile['profile_sub_category'])) {
            return $this->profile['profile_sub_category'];
        }
        return '';
    }

    /**
     * Get Profile Id
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
     * @todo: move store selection to profile, and get store, then get currency code
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
}
