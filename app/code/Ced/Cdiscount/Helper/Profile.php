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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class Profile For Cdiscount Profiling
 *
 * @package Ced\Cdiscount\Helper
 */
class Profile extends \Magento\Framework\App\Helper\AbstractHelper
{

    const ATTRIBUTE_CODE_PROFILE_ID = 'cdiscount_profile_id';

    const ATTRIBUTE_CODE_VALIDATION_ERRORS = 'cdiscount_validation_errors';

    const ATTRIBUTE_CODE_FEED_ERRORS = 'cdiscount_feed_errors';

    const ATTRIBUTE_TYPE_MODAL = 'model';

    const ATTRIBUTE_TYPE_OPTIONAL = 'optional';

    const ATTRIBUTE_TYPE_REQUIRED = 'required';

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
     * Cdiscount Attributes
     */
    public $attributes;

    /**
     * @var array
     */
    public $cdiscountAttribute = [];

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
    public $cdiscountCache;
    public $productRepository;

    /**
     * Profile constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Json\Helper\Data $json
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Ced\Cdiscount\Model\ProfileProductFactory $profileProduct,
        \Ced\Cdiscount\Model\ProfileFactory $profile,
        \Ced\Cdiscount\Helper\Cache $cache
    ) {
        $this->json = $json;
        $this->productRepository = $productRepository;
        $this->profileProduct = $profileProduct;
        $this->cdiscountCache = $cache;
        $this->profileFactory = $profile;
        parent::__construct($context);
    }

    /**
     * @param null $productId
     * @param null $profileId
     * @return $this
     * @throws \Exception
     */
    public function getProfile($productId = null, $profileId = null)
    {
        if ((empty($profileId) or !is_numeric($profileId)) and !empty($productId)) {
            $product = $this->productRepository
                ->getById($productId);
            $profileId = $product->getCustomAttribute(self::ATTRIBUTE_CODE_PROFILE_ID);
            if (!empty($profileId)) {
                $profileId = $profileId->getValue();
            }
        }
        if (!empty($profileId)) {
            $this->profile = $this->cdiscountCache->getValue(
                \Ced\Cdiscount\Helper\Cache::PROFILE_CACHE_KEY . $profileId
            );

            if (!isset($this->profile, $this->profile['id']) || empty($this->profile)) {
                $this->setProfile($profileId);
            }
        }

        return $this;
    }

    /**
     * @param null $profileId
     * @return bool
     * @throws \Exception
     */
    public function setProfile($profileId = null)
    {
        if (isset($profileId)) {
            $this->profile = $this->profileFactory->create()->load($profileId)->getData();
            if (isset($this->profile) and is_array($this->profile)) {
                $requiredAttributes = $this->json->jsonDecode($this->profile['profile_required_attributes']);
                //json to array conversion for getting all attributes options and mapping
                foreach ($requiredAttributes as $key => &$value) {
                    $parsedOptions = [];
                    $options = $this->json->jsonDecode($value['options']);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $parsedOptions[$option] = $option;
                        }
                    }

                    $value['options'] = $parsedOptions;
                    $value['option_mapping'] = $this->json->jsonDecode($value['option_mapping']);
                }
                $this->profile['profile_required_attributes'] = $requiredAttributes;

                $optionalAttributes = $this->json->jsonDecode($this->profile['profile_optional_attributes']);
                foreach ($optionalAttributes as &$value) {
                    $parsedOptions = [];
                    $options = $this->json->jsonDecode($value['options']);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $parsedOptions[$option] = $option;
                        }
                    }

                    $value['options'] = $parsedOptions;
                    $value['option_mapping'] = $this->json->jsonDecode($value['option_mapping']);
                }
                $this->profile['profile_optional_attributes'] = $optionalAttributes;

                $attributes = array_merge($requiredAttributes, $optionalAttributes);
                $this->profile['profile_attributes'] = $attributes;

                $this->cdiscountCache->setValue(
                    \Ced\Cdiscount\Helper\Cache::PROFILE_CACHE_KEY . $profileId,
                    $this->profile
                );
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
        $attributes = [];
        if (isset($this->profile['profile_attributes'])) {
            if ($type == self::ATTRIBUTE_TYPE_MODAL) {
                foreach ($this->profile['profile_attributes'] as $id => $attribute) {
                    if (isset($attribute['model_attributes']) && $attribute['model_attributes'] == true) {
                        $attributes[$id] = $attribute;
                    }
                }
            } elseif ($type == self::ATTRIBUTE_TYPE_OPTIONAL) {
                $attributes = $this->getOptionalAttributes();
            } elseif ($type == self::ATTRIBUTE_TYPE_REQUIRED) {
                $attributes = $this->getRequiredAttributes();
            } else {
                foreach ($this->profile['profile_attributes'] as $id => $attribute) {
                    $attributes[$id] = $attribute;
                }
            }
        }
        return $attributes;
    }

    public function getProductStatus()
    {
        $productState = isset($this->profile['product_state']) ? $this->profile['product_state'] : false;
        return $productState;
    }

    /**
     * Get Profile Attributes Indexed
     *
     * @return array
     */
    public function getRequiredAttributes()
    {
        if (isset($this->profile['profile_required_attributes'])) {
            $attributes = [];
            foreach ($this->profile['profile_required_attributes'] as $id => $attribute) {
                $attributes[$id] = $attribute;
            }
            return $attributes;
        }
        return [];
    }

    public function getOptionalAttributes()
    {
        if (isset($this->profile['profile_optional_attributes'])) {
            $attributes = [];
            foreach ($this->profile['profile_optional_attributes'] as $id => $attribute) {
                $attributes[$id] = $attribute;
            }
            return $attributes;
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

    public function getModelName()
    {
        if (isset($this->profile['model_name'])) {
            return $this->profile['model_name'];
        }
        return 'SOUMISSION CREATION PRODUITS_MK';
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
     * @param null $productId
     * @return array
     * @throws \Exception
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
