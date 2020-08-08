<?php
namespace Potato\Crawler\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Potato\Crawler\Model\Source\Protocol as ProtocolSource;
use Potato\Crawler\Model\Source\CustomerGroup as CustomerGroupSource;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Framework\UrlInterface;

class Config
{
    const GENERAL_ENABLED            = 'po_crawler/general/is_enabled';
    const GENERAL_ACCEPTABLE_CPU     = 'po_crawler/general/acceptable_cpu_load';
    const ADVANCED_STORE_PRIORITY    = 'po_crawler/priority/store_priority';
    const ADVANCED_PAGE_PRIORITY     = 'po_crawler/priority/page_group_priority';
    const ADVANCED_CURRENCY_PRIORITY = 'po_crawler/priority/currency_priority';
    const ADVANCED_CUSTOMER_GROUP    = 'po_crawler/priority/customer_group';
    const ADVANCED_PROTOCOL          = 'po_crawler/priority/protocol';
    const ADVANCED_USERAGENT         = 'po_crawler/advanced/useragent';
    const ADVANCED_SOURCE            = 'po_crawler/advanced/source';
    const ADVANCED_SOURCE_PATH       = 'po_crawler/advanced/source_path';
    const ADVANCED_USE_SHORT_PRODUCT_URL = 'po_crawler/advanced/short_product_url';
    const ADVANCED_DEBUG             = 'po_crawler/advanced/debug';
    const GENERAL_CRONJOB            = 'po_crawler/general/cronjob';
    const GENERAL_DEFAULT_CRONJOB    = '0 2 * * *';

    /** @var mixed|null */
    private $serializer = null;

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($store = null)
    {
        if (null === $store) {
            return (bool)$this->scopeConfig->getValue(
                self::GENERAL_ENABLED,
                'default'
            );
        }
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function useShortProductUrls($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ADVANCED_USE_SHORT_PRODUCT_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAcceptableCpu($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::GENERAL_ACCEPTABLE_CPU,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriority($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_STORE_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPages($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return $this->getMultiSelectOptionValues(self::ADVANCED_PAGE_PRIORITY, $store);
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrency($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $_result = $this->getMultiSelectOptionValues(self::ADVANCED_CURRENCY_PRIORITY, $store);
        if (empty(array_filter($_result))) {
            $store = $this->storeManager->getStore($store);
            $_result = [$store->getDefaultCurrencyCode()];
        }
        return $_result;
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroup($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $options = $this->getMultiSelectOptionValues(self::ADVANCED_CUSTOMER_GROUP, $store);
        $key = array_search(CustomerGroupSource::GUEST_VALUE, $options);
        if ($key != False) {
            $options[$key] = CustomerGroup::NOT_LOGGED_IN_ID;
        }
        return $options;
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProtocol($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return $this->getMultiSelectOptionValues(self::ADVANCED_PROTOCOL, $store);
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSource($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_SOURCE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourcePath($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::ADVANCED_SOURCE_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUserAgents($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $excludes = $this->scopeConfig->getValue(
            self::ADVANCED_USERAGENT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($this->serializer && @json_decode($excludes)) {
            $userAgentList = $this->serializer->unserialize($excludes);
        } else {
            $userAgentList = unserialize($excludes);
        }
        return $userAgentList;
    }

    /**
     * @param string $xmlPath
     * @param null|string|bool|int|StoreInterface $store
     * @return array
     */
    public function getMultiSelectOptionValues($xmlPath, $store = null)
    {
        $value = trim($this->scopeConfig->getValue(
            $xmlPath,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        $result = [];
        if (null !== $value && false !== $value) {
            $result = explode(',', $value);
        }
        return $result;
    }

    /**
     * @param null|string|bool|int|StoreInterface $store
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canDebug($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ADVANCED_DEBUG,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoresQueue()
    {
        $result = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive() || !$this->isEnabled($store)) {
                continue;
            }
            $priority = $this->generatePriority($this->getPriority($store), $result);
            $result[$priority] = $store;
        }
        ksort($result);
        return $result;
    }

    private function generatePriority($priority, $data)
    {
        while (array_key_exists($priority, $data)) {
            $priority++;
        }
        return $priority;
    }

    /**
     * @param StoreModel $store
     * @param string $protocol
     * @return string
     */
    public function getStoreBaseUrl($store, $protocol)
    {
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        if ($protocol == ProtocolSource::HTTPS_VALUE) {
            $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        }
        $baseUrl .= $store->getConfig(StoreModel::XML_PATH_USE_REWRITES) ? '' : 'index.php/';
        if ($store->getConfig(StoreModel::XML_PATH_STORE_IN_URL)) {
            $baseUrl = trim($baseUrl, '/');
            $baseUrl .= '/' . $store->getCode() . '/';
        }
        return $baseUrl;
    }

    /**
     * @param null $store
     * @return mixed|string
     */
    public function getCronJob($store=null)
    {
        $value = $this->scopeConfig->getValue(
            self::GENERAL_CRONJOB,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (empty($value)) {
            $value = self::GENERAL_DEFAULT_CRONJOB;
        }
        return $value;
    }
}