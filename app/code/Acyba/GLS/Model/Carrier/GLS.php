<?php

namespace Acyba\GLS\Model\Carrier;

use \Magento\Quote\Model\Quote\Address\RateResult\Error;
use \Magento\Quote\Model\Quote\Address\RateRequest;
use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use \Magento\Shipping\Model\Carrier\CarrierInterface;
use \Acyba\GLS\Helper\Tools;
use Magento\Framework\App\ResourceConnection;
use Owebia\AdvancedSettingCore\Model\Wrapper;
use Owebia\AdvancedShipping\Model\CallbackHandlerFactory;
use Owebia\AdvancedShipping\Model\Wrapper\RateResult as RateResultWrapper;

class GLS extends AbstractCarrierOnline implements CarrierInterface
{

    const CODE = 'gls';

    /**
     * Do not change variable name
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory = null;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory = null;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected $trackFactory = null;
    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $trackStatusFactory = null;

    /**
     * @var \Owebia\AdvancedSettingCore\Helper\Registry
     */
    protected $registryHelper = null;

    /**
     * @var \Owebia\AdvancedSettingCore\Helper\Config
     */
    protected $configHelper = null;

    /**
     * @var \Owebia\AdvancedSettingCore\Logger\Logger
     */
    protected $debugLogger = null;

    protected $callbackHandlerFactory;

    protected $_helperTools;

    protected $_resourceConnection;

    protected $rateRequest;


    /**
     * GLS constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Owebia\AdvancedSettingCore\Helper\Registry $registryHelper
     * @param \Owebia\AdvancedSettingCore\Helper\Config $configHelper
     * @param \Owebia\AdvancedSettingCore\Logger\Logger $debugLogger
     * @param Tools $helperTools
     * @param ResourceConnection $resourceConnection
     * @param RateRequest $rateRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,

        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Owebia\AdvancedSettingCore\Helper\Registry $registryHelper,
        \Owebia\AdvancedSettingCore\Helper\Config $configHelper,
        \Owebia\AdvancedSettingCore\Logger\Logger $debugLogger,
        Tools $helperTools,
        ResourceConnection $resourceConnection,
        RateRequest $rateRequest,
        CallbackHandlerFactory $callbackHandlerFactory,
        array $data = []
    )
    {
        parent::__construct($scopeInterface, $rateErrorFactory, $logger, $xmlSecurity, $xmlElFactory, $rateFactory,
            $rateMethodFactory, $trackFactory, $trackErrorFactory, $trackStatusFactory, $regionFactory, $countryFactory,
            $currencyFactory, $directoryData, $stockRegistry, $data);
        $this->objectManager = $objectManager;
        $this->rateFactory = $rateFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->trackFactory = $trackFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->registryHelper = $registryHelper;
        $this->configHelper = $configHelper;
        $this->debugLogger = $debugLogger;
        $this->_helperTools = $helperTools;
        $this->_resourceConnection = $resourceConnection;
        $this->rateRequest = $rateRequest;
        $this->callbackHandlerFactory = $callbackHandlerFactory;
    }

    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $trackingUrl = $this->_helperTools->getConfigValue('gls_tracking_view_url', 'gls', 'carriers');

        $result = $this->trackFactory->create();
        foreach ($trackings as $tracking) {
            $status = $this->trackStatusFactory->create();
            $status->setCarrier('gls');
            $status->setCarrierTitle('GLS');
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl(str_replace('{tracking_number}', $tracking, $trackingUrl));
            $result->append($status);
        }

        return $result;
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {

    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $config = $this->getConfig($request);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateFactory->create();

        if (!isset($config) || !is_array($config)) {
            $this->_helperTools->glsLog(__("GLS : Invalid config"));
            return false;
        }

        foreach ($config as $index => $item) {
            if ($item instanceof RateResultWrapper\Error) {
                $this->appendError($result, $item, $item->error);
            } elseif ($item instanceof RateResultWrapper\Method) {
                if ($item->enabled) {
                    $rate = $this->createMethod($index, $item);
                    $result->append($rate);
                }
            } else {
                $this->appendError($result, $item, "Invalid parsing result");
            }
        }

        return $result;
    }

    /**
     * @param string $methodId
     * @param RateResultWrapper\Method $method
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    protected function createMethod($methodId, RateResultWrapper\Method $method)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate */
        $rate = $this->rateMethodFactory->create();
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($methodId);
        $title = $method->title;
        $rate->setMethodTitle($title ? $title : 'N/A');
        $description = $method->description ? $method->description : null;
        $rate->setMethodDescription($description);
        $rate->setCost($method->price);
        $rate->setPrice($method->price);

        $rate->setCustomData($method->getCustomData());

        return $rate;
    }

    /**
     * @param \Magento\Shipping\Model\Rate\Result $result
     * @param mixed $wrapper
     * @param string $msg
     * @return $this
     */
    protected function appendError(\Magento\Shipping\Model\Rate\Result $result, $wrapper, $msg)
    {
        if (empty($wrapper->id) || $this->getConfigData('showmethod') != 0) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $methodTitle = !empty($wrapper->title)
                ? $wrapper->title
                : (!empty($wrapper->id) ? "Method `{$wrapper->id}` - " : '');
            $error->setErrorMessage("$methodTitle $msg");
            $result->append($error);
        }

        return $this;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array @apiz
     */
    public function getAllowedMethods()
    {
        $config = $this->getConfig($this->rateRequest);
        if (!isset($config) || !is_array($config)) {
            $this->_helperTools->glsLog(__("GLS : Invalid config"));
            return [];
        }

        $allowedMethods = [];
        foreach ($config as $index => $item) {
            if ($item instanceof RateResultWrapper\Method) {
                $allowedMethods[$index] = isset($item->title) ? $item->title : 'N/A';
            }
        }

        return $allowedMethods;
    }

    public function initRegistry(RateRequest $request = null)
    {
        $this->registryHelper->init($request);
        $this->registryHelper->register(
            'info',
            $this->registryHelper->create(
                Wrapper\Info::class,
                [
                    'carrierCode' => $this->getCarrierCode()
                ]
            )
        );
    }

    /**
     * @param RateRequest|null $request
     * @return mixed|null
     */
    public function getConfig(RateRequest $request = null)
    {
        if ($this->isDebugEnabled()) {
            $this->debugLogger->collapseOpen("Carrier[{$this->_code}].getConfig", 'panel-primary');
        }
        $config = null;
        try {
            $this->initRegistry($request);
            $configString = $this->getConfigsOfActivemethod();
            $callbackHandler = $this->callbackHandlerFactory->create();
            $callbackHandler->setRegistry($this->registryHelper);
            $this->configHelper->parse(
                $configString,
                $this->registryHelper,
                $callbackHandler,
                (bool)$this->getConfigData('debug')
            );
            $config = $callbackHandler->getParsingResult();

            if (array_key_exists('express_fr', $config) && !$this->glsIsExpressModeAvailable($request)) {
                $config['express_fr']->enabled = false;
            }
        } catch (\Exception $e) {
            $this->_logger->debug($e);
            if ($this->isDebugEnabled()) {
                $this->debugLogger->debug("Carrier[{$this->_code}].getConfig - Error - " . $e->getMessage());
            }
        }
        if ($this->isDebugEnabled()) {
            $this->debugLogger->collapseClose();
        }

        return $config;
    }

    /**
     * Get GLS methods configurations
     * @return string
     */
    protected function getConfigsOfActivemethod()
    {
        $methodsName = ['tohome', 'fds', 'relay', 'express'];
        $methods = [];
        foreach ($methodsName as $oneName) {
            if ($this->_helperTools->getConfigValue('gls_livraison' . $oneName, 'gls', 'carriers')) {
                $methodId = (int)$this->_helperTools->getConfigValue('gls_order' . $oneName, 'gls', 'carriers');
                $methods[$methodId] = 'gls_config' . $oneName;
            }
        }
        ksort($methods);
        $configString = '';
        foreach ($methods as $oneMethod) {
            $configString .= $this->getConfigData($oneMethod);
        }
        return $configString;
    }

    /**
     * @return boolean
     */
    protected function isDebugEnabled()
    {
        return (bool)$this->getConfigData('debug');
    }

    /**
     * @param RateRequest|null $request
     * @return bool
     */
    protected function glsIsExpressModeAvailable(RateRequest $request = null)
    {
        if (!is_null($request)) {
            $destPostCode = $request->getDestPostcode();
            $agencyCode = $this->_helperTools->getConfigValue('gls_agency_code', 'gls_general', 'gls_section');
            if (!empty($destPostCode) && !empty($agencyCode)) {
                $databaseConnection = $this->_resourceConnection->getConnection();
                $tableName = $this->_resourceConnection->getTableName('gls_agencies_list');
                $query = $databaseConnection->select()->from($tableName)->where('agencycode = ?', $agencyCode)->where('zipcode_start <= ?', $request->getDestPostcode())->where('zipcode_end >= ?', $request->getDestPostcode());
                $row = $databaseConnection->fetchRow($query);
                $idAgency = $row['id_agency_entry'];
                if ($idAgency) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isShippingLabelsAvailable()
    {
        return false;
    }
}
