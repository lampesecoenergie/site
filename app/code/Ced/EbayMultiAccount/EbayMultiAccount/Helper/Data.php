<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Ced\EbayMultiAccount\Model\Config\Location;
use Magento\Store\Model\StoreManagerInterface;
use Ced\EbayMultiAccount\Block\Extensions;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Message\Manager;

/**
 * Class Data
 * @package Ced\EbayMultiAccount\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var mixed
     */
    public $adminSession;
    /**
     * @var Manager
     */
    public $messageManager;
    /**
     * @var DirectoryList
     */
    public $directoryList;
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var int
     */
    public $compatLevel;
    /**
     * @var mixed
     */
    public $siteID;
    /**
     * @var mixed
     */
    public $devID;
    /**
     * @var mixed
     */
    public $environment;
    /**
     * @var mixed
     */
    public $token;
    /**
     * @var mixed
     */
    public $developer;

    /**
     * @var mixed
     */
    public $appId;

    /**
     * @var mixed
     */
    public $certID;
    /**
     * @var mixed
     */
    public $ruNameID;

    /**
     * @var Location
     */
    public $location;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var Feed
     */
    public $feedHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Config
     */
    public $configResourceModel;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    public $backendHelper;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param Manager $manager
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param Session $session
     * @param Filesystem $filesystem
     * @param Location $location
     * @param Feed $feedHelper
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param \Magento\Backend\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        Manager $manager,
        DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        Session $session,
        Filesystem $filesystem,
        Location $location,
        Feed $feedHelper,
        StoreManagerInterface $storeManager,
        Config $config,
        \Magento\Framework\UrlInterface $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->messageManager = $manager;
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->configResourceModel = $config;
        $this->backendHelper = $helper;
        $this->filesystem = $filesystem;
        $this->location = $location;
        $this->feedHelper = $feedHelper;
        $this->storeManager = $storeManager;
        $this->fileIo = $fileIo;
        $this->dateTime = $dateTime;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->timestamp = (string)$this->dateTime->gmtTimestamp();
        $this->devID = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/dev_id');
        $this->developer = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/dev_acc');
        $this->appId = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/app_id');
        $this->certID = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/cert_id');
        $this->ruNameID = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/ru_name');
        $this->compatLevel = 989;
    }

    public function updateAccountVariable()
    {
        $account = false;
        if ($this->_coreRegistry->registry('ebay_account')) {
            $account = $this->_coreRegistry->registry('ebay_account');
        }
        $this->environment = ($account) ? trim($account->getAccountEnv()) : '';
        $this->token = ($account) ? trim($account->getAccountToken()) : '';
        $this->siteID = ($account) ? trim($account->getAccountLocation()) : '';
    }


    /**
     * @return $this|bool
     */
    public function checkForLicence()
    {
        if ($this->_request->getModuleName() != 'ebaymultiaccount') {
            return $this;
        }
        $modules = $this->feedHelper->getCedCommerceExtensions();
        foreach ($modules as $moduleName => $releaseVersion) {
            $m = strtolower($moduleName);
            if (!preg_match('/ced/i', $m)) {
                return $this;
            }

            $h = $this->scopeConfig->getValue(Extensions::HASH_PATH_PREFIX . $m . '_hash');

            for ($i = 1; $i <= (int)$this->scopeConfig->getValue(Extensions::HASH_PATH_PREFIX . $m . '_level'); $i++) {
                $h = base64_decode($h);
            }

            $h = json_decode($h, true);
            if ($moduleName == "Magento2_Ced_EbayMultiAccount")
                if (is_array($h) && isset($h['domain']) && isset($h['module_name']) && isset($h['license']) && strtolower($h['module_name']) == $m && $h['license'] == $this->scopeConfig->getValue(\Ced\EbayMultiAccount\Block\Extensions::HASH_PATH_PREFIX . $m)) {
                    return $this;
                } else {
                    return false;
                }
        }
        return $this;
    }


    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        return $this->scopeConfig->getValue($path, 'store', $store->getCode());
    }

    /**
     * @param $requestBody
     * @param $call
     * @param $type
     * @return mixed
     */
    public function sendHttpRequest($requestBody, $call, $type)
    {
        $headers = $this->_buildEbayMultiAccountHeaders($call);
        $serverUrl = $this->getUrl($type);
        $connection = curl_init();
        /*print_r($headers);
        print_r($requestBody);die;*/
        curl_setopt($connection, CURLOPT_URL, $serverUrl);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_POST, 1);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $responseXml = curl_exec($connection);
        curl_close($connection);
        $response = false;
        if($responseXml != null)
            $response = $this->parseResponse($responseXml);
        return $response;
    }

    /**
     * @param $call
     * @return array
     */
    public function _buildEbayMultiAccountHeaders($call)
    {
        $headers = [
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->compatLevel,
            'X-EBAY-API-DEV-NAME: ' . $this->getDevid(),
            'X-EBAY-API-APP-NAME: ' . $this->getAppid(),
            'X-EBAY-API-CERT-NAME: ' . $this->getCertid(),
            'X-EBAY-API-CALL-NAME: ' . $call,
            'X-EBAY-API-SITEID: ' . $this->siteID,
        ];
        return $headers;
    }

    /**
     * @param null $appId
     * @return mixed|null|string
     */
    public function getDevid($devId = null)
    {
        if ($this->developer == "0") {
            $devId = 'a4d749e7-9b22-441e-8406-d3b65d95d41a';
        }
        if (empty($devId)) {
            $devId = $this->devID;
        }
        return $devId;
    }

    /**
     * @param null $appId
     * @return mixed|null|string
     */
    public function getAppid($appId = null)
    {
        if ($this->developer == "0") {
            if ($this->environment == "production") {
                $appId = 'PankajAs-GeforceI-PRD-2090330f6-233c7a3c';
            } else {
                $appId = 'PankajAs-GeforceI-SBX-345ed6035-93572cfa';
            }
        }
        if (empty($appId)) {
            $appId = $this->appId;
        }
        return $appId;
    }

    /**
     * @param null $certID
     * @return mixed|null|string
     */
    public function getCertid($certID = null)
    {
        if ($this->developer == "0") {
            if ($this->environment == "production") {
                $certID = 'PRD-090330f62955-96aa-4c19-b1df-6bf4';
            } else {
                $certID = 'SBX-45ed60356c5f-836b-4422-9202-6f4c';
            }
        }
        if (empty($certID)) {
            $certID = $this->certID;
        }
        return $certID;
    }

    /**
     * @param null $ruNameID
     * @return mixed|null|string
     */
    public function getRurlName($ruNameID = null)
    {
        if ($this->developer == "0") {
            if ($this->environment == "production") {
                $ruNameID = 'Pankaj_Aswal-PankajAs-Geforc-glteowd';
            } else {
                $ruNameID = 'Pankaj_Aswal-PankajAs-Geforc-ldlnmtua';
            }
        }
        if (empty($ruNameID)) {
            $ruNameID = $this->ruNameID;
        }
        return $ruNameID;
    }

    /**
     * @return array
     */
    public function getSessionId()
    {
        try {
            $result = [];
            $variable = "GetSessionID";
            $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
            $requestBody .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $requestBody .= "<Version>" . $this->compatLevel . "</Version>";
            $requestBody .= "<RuName>" . $this->getRurlName() . "</RuName>";
            $requestBody .= '</GetSessionIDRequest>';
            $response = $this->sendHttpRequest($requestBody, $variable, 'server');
            if (isset($response->Ack) && $response->Ack == 'Success') {
                $sessionID = $response->SessionID;
                $currentUrl = $this->backendHelper->getCurrentUrl();
                $param = ["url" => $currentUrl];
                $query = http_build_query($param);
                $sesId = urlencode($sessionID);
                $loginURL = $this->getUrl('login');
                $sessionId = $this->adminSession->getSessId();
                if (isset($sessionId)) {
                    $this->adminSession->unsSessId();
                }
                $this->adminSession->setSessId($response->SessionID);
                $result['data'] = $loginURL . "?SignIn&runame=" . $this->getRurlName() . "&SessID=" . $sesId . "&ruparams=" .$query;
                $result['msg'] = "success";
            } else {
                $result['data'] = isset($response->Errors->ShortMessage) ? $response->Errors->ShortMessage :
                    "Something went wrong";
                $result['msg'] = "error";
            }
        } catch (\Exception $e) {
            $result['data'] = $e->getMessage();
            $result['msg'] = "error";
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function fetchToken()
    {
        $data = '';
        $sessionId = $this->adminSession->getSessId();
        $variable = "FetchToken";
        $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestBody .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= "<SessionID>$sessionId</SessionID>";
        $requestBody .= '</FetchTokenRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $data = $response->eBayAuthToken;
        }
        return $data;
    }

    /**
     * @param int $level
     * @param null $ParentcatID
     * @return mixed|string
     */
    public function getCategories($level = 1, $ParentcatID = null)
    {
        $siteID = $this->siteID;
        $variable = "GetCategories";
        $token = $this->token;
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>";
        if ($ParentcatID) {
            $requestBody .= '<CategoryParent>' . $ParentcatID . '</CategoryParent>';
        }
        $requestBody .= '<CategorySiteID>' . $siteID . '</CategorySiteID>';
        $requestBody .= '<LevelLimit>' . $level . '</LevelLimit>';
        $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestBody .= '</GetCategoriesRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            return $response;
        } else {
            return "error";
        }
    }

    /**
     * @param $catID
     * @return mixed|string
     */
    public function getCatSpecificAttribute($catID)
    {
        $variable = "GetCategorySpecifics";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategorySpecificsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= '<WarningLevel>High</WarningLevel>';
        $requestBody .= '<CategorySpecific><CategoryID>' . $catID . '</CategoryID></CategorySpecific>';
        $requestBody .= '<RequesterCredentials><eBayAuthToken>' . $this->token . '</eBayAuthToken></RequesterCredentials>';
        $requestBody .= '<MaxValuesPerName>5000</MaxValuesPerName>';
        $requestBody .= '</GetCategorySpecificsRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            return $response;
        } else {
            return 'error';
        }
    }

    /**
     * @param $catID
     * @param $limits
     * @return mixed|string
     */
    public function getCategoryFeatures($catID, $limits)
    {
        $variable = "GetCategoryFeatures";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= '<RequesterCredentials><eBayAuthToken>' . $this->token . '</eBayAuthToken></RequesterCredentials>';
        $requestBody .= '<CategoryID>' . $catID . '</CategoryID>';
        $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestBody .= '<ViewAllNodes>true</ViewAllNodes>';
        if (is_array($limits) && !empty($limits)) {
            foreach ($limits as $limit) {
                $requestBody .= '<FeatureID>' . $limit . '</FeatureID>';
            }
        }
        $requestBody .= '</GetCategoryFeaturesRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            return $response;
        } else {
            return "error";
        }
    }

    /**
     * @return string
     */
    public function getSiteSpecificExcludedLocations()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                          <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                        </RequesterCredentials>
                        <DetailName>ExcludeShippingLocationDetails</DetailName>
                      </GeteBayDetailsRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = json_encode($response->ExcludeShippingLocationDetails);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getCountryAndCountry()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                          <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                        </RequesterCredentials>
                        <DetailName>CountryDetails</DetailName>
                        <DetailName>CurrencyDetails</DetailName>
                      </GeteBayDetailsRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = json_encode($response);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSiteSpecificPaymentMethods()
    {
        $variable = "GetCategoryFeatures";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                    <GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                      <RequesterCredentials>
                        <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                      </RequesterCredentials>
                      <DetailLevel>ReturnAll</DetailLevel>
                      <FeatureID>PaymentMethods</FeatureID>
                    </GetCategoryFeaturesRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = json_encode($response->SiteDefaults->PaymentMethod);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSiteSpecificReturnPolicy()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                          <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                        </RequesterCredentials>
                        <DetailName>ReturnPolicyDetails</DetailName>
                      </GeteBayDetailsRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = json_encode($response);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSiteSpecificShippingDetails()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?> 
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
                        <RequesterCredentials> 
                          <eBayAuthToken>' . $this->token . '</eBayAuthToken> 
                        </RequesterCredentials> 
                        <DetailName>ShippingCarrierDetails</DetailName> 
                        <DetailName>ShippingServiceDetails</DetailName> 
                      </GeteBayDetailsRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = json_encode($response);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getOrderRequestBody()
    {
        if ($this->token) {
            $days = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/from_order');
            $startDate = date('Y-m-d', strtotime('-' . $days . ' days', time()));
            $orderFrom = gmdate("Y-m-d\TH:i:s", strtotime($startDate));
            $orderTo = gmdate("Y-m-d\TH:i:s");
            $variable = "GetOrders";
            $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
            $requestBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
            $requestBody .= "<CreateTimeFrom>" . $orderFrom . "</CreateTimeFrom><CreateTimeTo>" . $orderTo .
                "</CreateTimeTo>";
            $requestBody .= '<OrderRole>Seller</OrderRole><OrderStatus>Completed</OrderStatus>';
            $requestBody .= "<RequesterCredentials><eBayAuthToken>" . $this->token .
                "</eBayAuthToken></RequesterCredentials>";
            $requestBody .= '</GetOrdersRequest>';
            $response = $this->sendHttpRequest($requestBody, $variable, 'server');

            if (isset($response->Ack) && ($response->Ack == 'Success' || $response->Ack == 'Warning')) {
                $result = json_encode($response->OrderArray);
            } else {
                $result = json_encode($response);
            }
        } else {
            $result = 'please fetch the token';
        }
        return $result;
    }

    /**
     * @param $ebaymultiaccountOrderId
     * @param $trackNumber
     * @param $shippingCarrierUsed
     * @param $deliverydate
     * @param $shipment
     * @return string
     */
    public function createShipmentOrderBody($ebaymultiaccountOrderId, $trackNumber, $shippingCarrierUsed, $deliverydate, $shipment)
    {
        $variable = "CompleteSale";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                    <CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                            <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                        </RequesterCredentials>
                        <WarningLevel>High</WarningLevel>
                        <OrderID>' . $ebaymultiaccountOrderId . '</OrderID>
                        <Shipment>
                            <ShipmentTrackingDetails>
                              <ShipmentTrackingNumber>' . $trackNumber . '</ShipmentTrackingNumber>
                              <ShippingCarrierUsed>' . $shippingCarrierUsed . '</ShippingCarrierUsed>
                            </ShipmentTrackingDetails>
                            <ShippedTime>' . $deliverydate . '</ShippedTime>
                        </Shipment>
                        <Shipped>' . $shipment . '</Shipped>
                        <TransactionID>0</TransactionID>
                    </CompleteSaleRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack == 'Success') {
            $result = 'Success';
        } else {
            $result = isset($response->Errors->LongMessage) ? $response->Errors->LongMessage : json_encode($response);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getShhipingDetails()
    {
        $shippingDetails = [];
        $locationName = '';
        $locationList = $this->location->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $this->siteID) {
                $locationName = $value['label'];
            }
        }
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/ebaymultiaccount/');
        $path = $folderPath . $locationName . '/shippingDetails.json';
        if (file_exists($folderPath . $locationName)) {
            $shippingDetails = $this->loadFile($path, '', '');
        }
        return $shippingDetails;
    }

    /**
     * @param $path
     * @param string $code
     * @param string $type
     * @return bool|mixed|string
     */
    public function loadFile($path, $code = '', $type = '')
    {
        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }
        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    $data = empty($type) ? $this->json->jsonDecode($data) : $data;
                    return $data;
                }
            }
        }
        return false;
    }

    /**
     * @return array|bool|mixed|string
     */
    public function returnPolicyValue()
    {
        $locationName = '';
        $locationList = $this->location->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $this->siteID) {
                $locationName = $value['label'];
            }
        }
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/ebaymultiaccount/');
        $path = $folderPath . $locationName . '/returnPolicy.json';
        if (file_exists($folderPath . $locationName)) {
            $data = $this->loadFile($path, '', '');
        } else {
            $data = [];
        }
        return $data;
    }

    /**
     * @param $type
     * @param null $env
     * @return string
     */
    public function getUrl($type, $env = null)
    {
        $env = $env == null ? $this->environment : $env;
        if ($env == "production") {
            switch ($type) {
                case 'server':
                    return "https://api.ebay.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.ebay.com/ws/eBayISAPI.dll";
                    break;

                case 'finding':
                    return "http://svcs.ebay.com/services/search/FindingService/v1";
                    break;

                case 'shopping':
                    return "http://open.api.ebay.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.ebay.com/ws/eBayISAPI.dll";
                    break;

                default:
                    return "https://api.ebay.com/ws/api.dll";
                    break;
            }
        } else {
            switch ($type) {
                case 'server':
                    return "https://api.sandbox.ebay.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.sandbox.ebay.com/ws/eBayISAPI.dll";
                    break;

                case 'finding':
                    return "http://svcs.sandbox.ebay.com/services/search/FindingService/v1";
                    break;

                case 'shopping':
                    return "http://open.api.sandbox.ebay.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.sandbox.ebay.com/ws/eBayISAPI.dll";
                    break;

                default:
                    return "https://api.sandbox.ebay.com/ws/api.dll";
                    break;
            }
        }
    }

    /**
     * @param $siteId
     * @return array
     */
    public function getEbayMultiAccountsites($siteId)
    {
        $site = [];
        switch ($siteId) {
            case '0':
                $site['name'] = "US";
                $site['currency'] = ['USD'];
                $site['abbreviation'] = "US";
                break;
            case '2':
                $site['name'] = "Canada";
                $site['currency'] = ['CAD', 'USD'];
                $site['abbreviation'] = "CA";
                break;
            case '3':
                $site['name'] = "UK";
                $site['currency'] = ['GBR'];
                $site['abbreviation'] = "GB";
                break;
            case '15':
                $site['name'] = "Australia";
                $site['currency'] = ['AUD'];
                $site['abbreviation'] = "AU";
                break;
            case '16':
                $site['name'] = "Austria";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "AT";
                break;
            case '23':
                $site['name'] = "Belgium_French";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "BEFR";
                break;
            case '71':
                $site['name'] = "France";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "FR";
                break;
            case '77':
                $site['name'] = "Germany";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "DE";
                break;
            case '101':
                $site['name'] = "Italy";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "IT";
                break;
            case '123':
                $site['name'] = "Belgium_Dutch";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "BENL";
                break;
            case '146':
                $site['name'] = "Netherlands";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "NL";
                break;
            case '186':
                $site['name'] = "Spain";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "ES";
                break;
            case '193':
                $site['name'] = "Switzerland";
                $site['currency'] = ['CHF'];
                $site['abbreviation'] = "CH";
                break;
            case '201':
                $site['name'] = "HongKong";
                $site['currency'] = ['HKD'];
                $site['abbreviation'] = "HK";
                break;
            case '203':
                $site['name'] = "India";
                $site['currency'] = ['INR'];
                $site['abbreviation'] = "IN";
                break;
            case '205':
                $site['name'] = "Ireland";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "IE";
                break;
            case '207':
                $site['name'] = "Malaysia";
                $site['currency'] = ['MYR'];
                $site['abbreviation'] = "MY";
                break;
            case '210':
                $site['name'] = "CanadaFrench";
                $site['currency'] = ['CAD', 'USD'];
                $site['abbreviation'] = "CAFR";
                break;
            case '211':
                $site['name'] = "Philippines";
                $site['currency'] = ['PHP'];
                $site['abbreviation'] = "PH";
                break;
            case '212':
                $site['name'] = "Poland";
                $site['currency'] = ['PLN'];
                $site['abbreviation'] = "PL";
                break;
            case '216':
                $site['name'] = "Singapore";
                $site['currency'] = ['SGD'];
                $site['abbreviation'] = "SG";
                break;
            default:
                $site = [];
                break;
        }
        return $site;
    }

    public function responseParse($response = '', $type = null, $filePath = '')
    {
        if ($type) {
            try {
                $accountId = 0;
                $currentAccount = $this->_coreRegistry->registry('ebay_account');
                if($currentAccount) {
                    $accountId = $currentAccount->getId();
                }
                $feedModel = $this->objectManager->create('\Ced\EbayMultiAccount\Model\Feeds');
                $feedModel->setData('feed_date', date('Y-m-d H:i:s'));
                $feedModel->setData('feed_type', $type);
                $feedModel->setData('feed_source', isset($response->Ack) ? $response->Ack : 'Unknown');
                $feedModel->setData('feed_errors', $this->json->jsonEncode($response));
                $feedModel->setData('feed_file', $filePath);
                $feedModel->setData('account_id', $accountId);
                $feedModel->save();
                return true;
            } catch (\Exception $e) {
                return false;
            }

        }
        return true;
    }

    /**
     * Create fruugo directory in the specified root directory.
     * used for storing json/xml files to be synced.
     * @param string $name
     * @param string $code
     * @return array|string
     */
    public function createDir($name = 'ebaymultiaccount', $code='var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if (file_exists($path)) {
            return ['status' => true,'path' => $path, 'action' => 'dir_exists'];
        } else {
            try
            {
                $this->fileIo->mkdir($path, 0775, true);
                return  ['status' => true,'path' => $path,  'action' => 'dir_created'];
            }
            catch (\Exception $e){
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    public function createFeed($finalData = null, $variable)
    {
        $path = $this->createDir('ebaymultiaccount/' . $variable, 'media');
        $path = $path['path'] . '/' . $variable . '_' . $this->timestamp . '.xml';
        $handle = fopen($path, 'w');
        $finalData = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
            $finalData);
        fwrite($handle, htmlspecialchars_decode($finalData));
        fclose($handle);
        return $path;
    }

    /**
     * @param $responseXml
     * @return mixed
     */
    public function ParseResponse($responseXml)
    {
        $sxe = new \SimpleXMLElement ($responseXml);
        return $res = json_decode(json_encode($sxe));
    }

    public function setAccountSession() {
        $accountId = '';
        $this->adminSession->unsAccountId();
        $params = $this->_getRequest()->getParams();
        if(isset($params['account_id']) && $params['account_id'] > 0) {
            $accountId = $params['account_id'];
        } else {
            $accountId = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/primary_account');
            if(!$accountId) {
                $accounts = $this->multiAccountHelper->getAllAccounts();
                if($accounts) {
                    $accountId = $accounts->getFirstItem()->getId();
                }
            }
        }
        $this->adminSession->setAccountId($accountId);
        return $accountId;
    }

    public function getAccountSession() {
        $accountId = '';
        $accountId = $this->adminSession->getAccountId();
        if(!$accountId) {
            $accountId = $this->setAccountSession();
        }
        return $accountId;
    }

    /**
     * @param null $page
     * @return array|string
     */
    public function importProduct($page = null)
    {
        $result = [];
        $importFieldMappings[] = array(
            'ebay_attribute' => 'SKU',
            'magento_attribute' => 'sku'
        );
        $page = empty($page) ? 1 : $page;
        $account = $this->_coreRegistry->registry('ebay_account');
        $accountId = $account->getId();
        $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
        $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $importFieldMapping = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/import_field_mapping');
        if($importFieldMapping && $importFieldMapping != null) {
            $importFieldMappings = json_decode($importFieldMapping, true);
        }
        if (empty($this->token)) {
            $result = "Please fetch the token";
        } else {
            $variable = "GetMyeBaySelling";
            $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                            <GetMyeBaySellingRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                              <RequesterCredentials>
                                <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                              </RequesterCredentials>  
                              <ActiveList>
                                <Sort>TimeLeft</Sort>
                                <Pagination>
                                 <EntriesPerPage>100</EntriesPerPage>
                                  <PageNumber>' . $page . '</PageNumber>
                                </Pagination>
                              </ActiveList>
                            </GetMyeBaySellingRequest>';
            $response = $this->sendHttpRequest($requestBody, $variable, 'server');

            if (isset($response->Ack) && $response->Ack == 'Success') {
                if (isset($response->ActiveList->ItemArray)) {
                    foreach ($response->ActiveList->ItemArray->Item as $item) {
                        if (isset($item->SKU) && isset($item->ItemID)) {
                            foreach ($importFieldMappings as $importField) {
                                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute($importField['magento_attribute'], $item->{$importField['ebay_attribute']});
                                if($product)
                                    break;
                            }
                            $ebayItemId = $item->ItemID;
                            if($product) {
                                $product->setData($prodStatusAccAttr, 4);
                                $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                $product->setData($itemIdAccAttr, $ebayItemId);
                                $product->getResource()->saveAttribute($product, $itemIdAccAttr)->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                $successids[] = $product->getSku();
                            } else {
                                $failureids[] = $item->ItemID;
                            }
                        } else {
                            $failureids[] = $item->ItemID;
                        }
                    }
                    $totalQty = $response->ActiveList->PaginationResult->TotalNumberOfEntries;
                    $result['check'] = (int)$totalQty > 100 * $page ? "continue" : '';
                    if(isset($successids) && is_array($successids) && count($successids) > 0) {
                        $result['success'] = "Successfully Imported SKU's" . implode(', ', $successids);
                    } else if(isset($failureids) && is_array($failureids) && count($failureids) > 0) {
                        $result['error'] = "Product not found for Item Ids : " . implode(', ', $failureids);
                    }
                }
            } else {
                $result['error'] = $response->errorMessage;
                $result['check'] = "continue";
            }
        }
        return $result;
    }
}
