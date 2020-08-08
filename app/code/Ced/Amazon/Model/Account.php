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
 * @package   Ced_Amazon
 * @author    CedCommerce Amazon Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

class Account extends \Magento\Framework\Model\AbstractModel implements \Ced\Amazon\Api\Data\AccountInterface
{
    const ID_FIELD_NAME = 'id';
    const NAME = 'ced_amazon_account';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_ACTIVE = 'active';
    const COLUMN_STATUS = 'status';

    const COLUMN_CEDCOMMERCE = 'cedcommerce';
    const COLUMN_SELLER_ID = 'seller_id';
    const COLUMN_MARKETPLACE = 'marketplace';
    const COLUMN_ACCESS_KEY_ID = 'aws_access_key_id';
    const COLUMN_AWS_AUTH_ID = 'aws_auth_id';
    const COLUMN_SECRET_KEY = 'secret_key';

    const COLUMN_MULTI_STORE = 'multi_store';
    const COLUMN_MULTI_STORE_VALUES = 'multi_store_values';
    const COLUMN_CHANNEL = 'channel';
    const COLUMN_STORE_ID = 'store_id';
    const COLUMN_SHIPPING_METHOD = 'shipping_method';
    const COLUMN_PAYMENT_METHOD = 'payment_method';

    const COLUMN_NOTES = 'notes';
    const COLUMN_MODE = 'mode';

    const MODE_LIVE = 'live';
    const MODE_MOCK = 'mock';

    const DEVELOPER = [
        \Amazon\Sdk\Marketplace::REGION_INDIA => [
            self::COLUMN_SECRET_KEY => 'LG+YTgrUoKFZxE9kcbB43/Blak2uNyV1ZhTi3fBW',
            self::COLUMN_ACCESS_KEY_ID => 'AKIAIPDG6VXASDFKIOPA'
        ],
        \Amazon\Sdk\Marketplace::REGION_EUROPE => [
            self::COLUMN_SECRET_KEY => 'xhkbEvpyIpYhZCdSdYtTsvFsY2y/E62YjE8SS2XT',
            self::COLUMN_ACCESS_KEY_ID => 'AKIAING3ZK7YQ2WCTF6A'
        ],
        \Amazon\Sdk\Marketplace::REGION_NORTH_AMERICA => [
            self::COLUMN_SECRET_KEY => 'sPsyLK1ApPc8mG7hOLbMLs0x/tW/Yq6tcupLXyUC',
            self::COLUMN_ACCESS_KEY_ID => 'AKIAJDZBBQNFL3NDI2MA'
        ],
        \Amazon\Sdk\Marketplace::REGION_AUSTRALIA => [
            self::COLUMN_SECRET_KEY => 'iNyUNxPYwvWZt4CWdESHj/RaT2ItnR7GJvpRuz1P',
            self::COLUMN_ACCESS_KEY_ID => 'AKIAJASLN5XVC5HWNJTQ'
        ]
    ];

    public $configFactory;

    public function __construct(
        \Amazon\Sdk\Api\ConfigFactory $configFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configFactory = $configFactory;
    }

    /**
     * @return  void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Account::class);
    }

    public static function getCedcommerceAccessKeyId($marketplaceId)
    {
        $accessKeyId = null;
        $region = \Amazon\Sdk\Marketplace::getRegionByMarketplaceId($marketplaceId);
        if (isset($region, self::DEVELOPER[$region])) {
            $accessKeyId = self::DEVELOPER[$region][self::COLUMN_ACCESS_KEY_ID];
        }

        return $accessKeyId;
    }

    public static function getCedcommerceSecretKey($marketplaceId)
    {
        $secretKey = null;
        $region = \Amazon\Sdk\Marketplace::getRegionByMarketplaceId($marketplaceId);
        if (isset($region, self::DEVELOPER[$region])) {
            $secretKey = self::DEVELOPER[$region][self::COLUMN_SECRET_KEY];
        }

        return $secretKey;
    }

    public static function isCedcommerce($accessKeyId)
    {
        $result = false;
        foreach (self::DEVELOPER as $credential) {
            if (isset($credential[self::COLUMN_ACCESS_KEY_ID]) &&
                $credential[self::COLUMN_ACCESS_KEY_ID] == $accessKeyId) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function getName()
    {
        $name = $this->getData(\Ced\Amazon\Model\Account::COLUMN_NAME);
        return $name;
    }

    public function setName($name)
    {
        return $this->setData(\Ced\Amazon\Model\Account::COLUMN_NAME, $name);
    }

    /**
     * Get Config for the Account
     * @param array $marketplaceIds
     * @return \Amazon\Sdk\Api\Config|mixed
     */
    public function getConfig($marketplaceIds = [])
    {
        $accountMarketplaceIds = explode(',', $this->getData(self::COLUMN_MARKETPLACE));
        if (!empty($marketplaceIds) && is_array($marketplaceIds)) {
            $accountMarketplaceIds = array_intersect($accountMarketplaceIds, $marketplaceIds);
        }

        $cedcommerce = $this->getData(\Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE);
        $accessKeyId = $this->getData(\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID);
        $secretKey = $this->getData(\Ced\Amazon\Model\Account::COLUMN_SECRET_KEY);
        if ($cedcommerce) {
            // TODO: use exact accessKeyId for marketplace, as US and EU together will result in failure.
            foreach ($accountMarketplaceIds as $marketplaceId) {
                $accessKeyId = \Ced\Amazon\Model\Account::getCedcommerceAccessKeyId($marketplaceId);
                $secretKey = \Ced\Amazon\Model\Account::getCedcommerceSecretKey($marketplaceId);
                if (isset($accessKeyId, $secretKey)) {
                    break;
                }
            }
        }

        $token = $this->getData(self::COLUMN_AWS_AUTH_ID);
        $params = [
            'sellerId' => $this->getData(self::COLUMN_SELLER_ID),
            'marketplaceId' => $accountMarketplaceIds,
            'accessKeyId' => $accessKeyId,
            'secretKey' => $secretKey,
            'muteLog' => false
        ];

        if (!empty($token) && strpos($token, 'amzn.mws.') !== false) {
            $params['token'] = $token;
        }

        /** @var \Amazon\Sdk\Api\Config $config */
        $config = $this->configFactory->create(['params' => $params]);

        return $config;
    }

    public function getMockMode()
    {
        $mode = $this->getData(\Ced\Amazon\Model\Account::COLUMN_MODE);
        if ($mode == self::MODE_MOCK) {
            return true;
        }

        return false;
    }

    /**
     * Get StoreId from account
     * @param null $marketplaceId
     * @return int
     */
    public function getStore($marketplaceId = null)
    {
        // Getting Default Set Store
        $storeId = $this->getData(\Ced\Amazon\Model\Account::COLUMN_STORE_ID);

        // Default Admin store 0 is used
        if (!isset($storeId)) {
            $storeId = 0;
        }

        // For marketplace-wise multi store mapping
        if (isset($marketplaceId) && $this->getMultiStore()) {
            $stores = $this->getMultiStoreValues();
            foreach ($stores as $store) {
                if (isset($store['marketplace'], $store['store_id']) &&
                    $store['marketplace'] == $marketplaceId) {
                    $storeId = $store['store_id'];
                    break;
                }
            }
        }

        return $storeId;
    }

    public function getDefaultMarketplace()
    {
        $marketplaceId = '';
        $marketplace = $this->getData(\Ced\Amazon\Model\Account::COLUMN_MARKETPLACE);
        if (!empty($marketplace)) {
            $marketplaceIds = explode(',', $marketplace);
            if (isset($marketplaceIds[0])) {
                $marketplaceId = $marketplaceIds[0];
            }
        }

        return $marketplaceId;
    }

    public function getMarketplaceIds()
    {
        $marketplaceIds = [];
        $marketplace = $this->getData(\Ced\Amazon\Model\Account::COLUMN_MARKETPLACE);
        if (!empty($marketplace)) {
            $marketplaceIds = explode(',', $marketplace);
        }

        return $marketplaceIds;
    }

    /**
     * Get account store id
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getData(\Ced\Amazon\Model\Account::COLUMN_STORE_ID);
        return $storeId;
    }

    /**
     * Get Mode
     * @return string
     */
    public function getMode()
    {
        $mode = $this->getData(\Ced\Amazon\Model\Account::COLUMN_MODE);
        return $mode;
    }

    /**
     * Get Order Channel (AFN/MFN).
     * @return string
     */
    public function getChannel()
    {
        $channel = $this->getData(\Ced\Amazon\Model\Account::COLUMN_CHANNEL);
        return $channel;
    }

    /**
     * Set Order Channel (AFN/MFN)
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        return $this->setData(\Ced\Amazon\Model\Account::COLUMN_CHANNEL, $channel);
    }

    /**
     * Get Shipping Method
     * @return string
     */
    public function getShippingMethod()
    {
        $method = $this->getData(\Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD);
        if (empty($method)) {
            $method = \Ced\Amazon\Model\Carrier\Shipbyamazon::METHOD_NAME_CODE;
        }

        return $method;
    }

    /**
     * Set Shipping Method
     * @param string $method
     * @return $this
     */
    public function setShippingMethod($method)
    {
        return $this->setData(\Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD, $method);
    }

    /**
     * Get Payment Method
     * @return string
     */
    public function getPaymentMethod()
    {
        $method = $this->getData(\Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD);
        if (empty($method)) {
            $method = \Ced\Amazon\Model\Payment\Paybyamazon::METHOD_CODE;
        }

        return $method;
    }

    /**
     * Set Payment Method
     * @param string $method
     * @return $this
     */
    public function setPaymentMethod($method)
    {
        return $this->setData(\Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD, $method);
    }

    /**
     * Get Multi Store Flag
     * @return boolean
     */
    public function getMultiStore()
    {
        return $this->getData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE);
    }

    /**
     * Get Marketplace to Store Mapping as Array
     * @return mixed
     */
    public function getMultiStoreValues()
    {
        $stores = $this->getData(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES);
        if (!empty($stores)) {
            $stores = json_decode($stores, true);
        }

        if (!is_array($stores)) {
            $stores = [];
        }

        return $stores;
    }

    public function getCustomerGroup($marketplaceId = null)
    {
        $groupId = null;
        // For marketplace-wise multi store mapping
        if (isset($marketplaceId) && $this->getMultiStore()) {
            $groups = $this->getMultiStoreValues();
            foreach ($groups as $group) {
                if (isset($group['marketplace'], $group['group_id']) &&
                    $group['marketplace'] == $marketplaceId) {
                    $groupId = $group['group_id'];
                    break;
                }
            }
        }

        return $groupId;
    }
}
