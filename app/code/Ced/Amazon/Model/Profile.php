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

namespace Ced\Amazon\Model;

class Profile extends \Magento\Framework\Model\AbstractModel implements \Ced\Amazon\Api\Data\ProfileInterface
{
    const NAME = 'ced_amazon_profile';

    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'profile_name';
    const COLUMN_CODE = 'profile_code';
    const COLUMN_STATUS = 'profile_status';

    const COLUMN_CATEGORY = 'profile_category';
    const COLUMN_SUB_CATEGORY = 'profile_sub_category';
    const COLUMN_ATTRIBUTES = 'profile_attributes'; // merged values, not saved.
    const COLUMN_REQUIRED_ATTRIBUTES = 'profile_required_attributes';
    const COLUMN_OPTIONAL_ATTRIBUTES = 'profile_optional_attributes';

    const COLUMN_MARKETPLACE = 'marketplace';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_STORE_ID = 'store_id';
    const COLUMN_QUERY = 'query';

    const COLUMN_FILTER = 'filter'; // Removed. Do not use.

    const COLUMN_BARCODE_EXCEMPTION = 'barcode_exemption';

    const COLUMN_REQUIRED = [
        self::COLUMN_NAME,
        self::COLUMN_MARKETPLACE,
        self::COLUMN_ACCOUNT_ID,
        self::COLUMN_CATEGORY,
        self::COLUMN_SUB_CATEGORY,
    ];

    public $processed = false;

    /** @var \Magento\Store\Model\StoreManagerInterface  */
    public $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
    }

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Profile::class);
    }

    public function getMarketplace()
    {
        $marketplace = $this->getData(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE);
        return $marketplace;
    }

    public function getMarketplaceIds()
    {
        $marketplaceIds = [];
        $marketplace = $this->getMarketplace();
        if (!empty($marketplace)) {
            $marketplaceIds = explode(',', $marketplace);
        }

        return $marketplaceIds;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::COLUMN_NAME);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::COLUMN_NAME, $name);
    }

    public function getAccountId()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::COLUMN_STORE_ID);
    }

    public function afterLoad()
    {
        $this->processAttributes();

        parent::afterLoad();
    }

    /**
     * TODO: recheck if already encoded.
     */
    public function beforeSave()
    {
        $required = $this->getData(\Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES);
        $optional = $this->getData(\Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES);

        if (is_array($required)) {
            $this->setData(\Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES, json_encode($required));
        }

        if (is_array($optional)) {
            $this->setData(\Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES, json_encode($optional));
        }

        parent::beforeSave();
    }

    private function processAttributes()
    {
        $attributes = [];
        $required = $this->getData(\Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES);
        $required = !empty($required) ? json_decode($required, true) : [];

        $optional = $this->getData(\Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES);
        $optional = !empty($optional) ? json_decode($optional, true) : [];

        if (is_array($required)) {
            $attributes = array_merge($attributes, $required);
            $this->setData(\Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES, $required);
        }

        if (is_array($optional)) {
            $attributes = array_merge($attributes, $optional);
            $this->setData(\Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES, $optional);
        }

        if (is_array($attributes)) {
            $this->setData(\Ced\Amazon\Model\Profile::COLUMN_ATTRIBUTES, $attributes);
        }

        $this->processed = true;
    }

    /**
     * Set Status
     * @param int $status
     * @return $this
     */
    public function setProfileSatus($status)
    {
        return $this->setData(self::COLUMN_STATUS, $status);
    }

    /**
     * Get Profile Store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        if ($this->storeManager->isSingleStoreMode()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $store = $this->storeManager->getStore($storeId);
        return $store;
    }

    /**
     * Get Profile Category
     * @return string
     */
    public function getProfileCategory()
    {
        return $this->getData(self::COLUMN_CATEGORY);
    }

    /**
     * Get Profile Sub Category
     * @return string
     */
    public function getProfileSubCategory()
    {
        return $this->getData(self::COLUMN_SUB_CATEGORY);
    }

    /**
     * Get Profile Attributes
     * @return array
     */
    public function getProfileAttributes()
    {
        $attributes = $this->getData(self::COLUMN_ATTRIBUTES);
        if (!is_array($attributes)) {
            $attributes = [];
        }

        return $attributes;
    }

    /**
     * Get Barcode Exemption
     * @return bool
     */
    public function getBarcodeExemption()
    {
        return $this->getData(self::COLUMN_BARCODE_EXCEMPTION);
    }

    /**
     * Set Barcode Exemption
     * @param boolean $value
     * @return $this
     */
    public function setBarcodeExemption($value)
    {
        return $this->setData(self::COLUMN_BARCODE_EXCEMPTION, $value);
    }
}
