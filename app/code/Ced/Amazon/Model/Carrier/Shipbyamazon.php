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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Amazon\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
 
class Shipbyamazon extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const REGISTRY_INDEX_SHIPPING_TOTAL = 'amazon_shipping_total';
    const REGISTRY_INDEX_SHIPPING_METHOD = 'amazon_shipping_method';
    const METHOD_NAME_CODE = 'shipbyamazon_shipbyamazon';

    const METHOD_CODE = 'shipbyamazon';
    const CARRIER_CODE = 'shipbyamazon';
    const CARRIER_TITLE = 'Amazon Shipping Method';

    /**
     * @var string
     */
    public $_code = 'shipbyamazon';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $_logger;
    /**
     * @var bool
     */
    public $_isFixed = true;
    /**
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    public $_rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    public $_rateMethodFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /** @var \Magento\Framework\App\State  */
    public $appState;

    /**
     * Shipbyamazon constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->appState = $appState;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        if ($this->appState->getAreaCode() != 'adminhtml' && $this->appState->getAreaCode() != 'crontab') {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);
        
        $method->setCarrierTitle($this->getConfigData('title'));

        $title = $this->registry->registry(self::REGISTRY_INDEX_SHIPPING_METHOD);
        $method->setMethodTitle($title);

        $price = $this->registry->registry(self::REGISTRY_INDEX_SHIPPING_TOTAL);
        if (!isset($price)) {
            $price = 0;
        }
        $method->setPrice($price);
        $method->setCost(0);
        
        $result->append($method);
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => $this->getConfigData('title')];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }
}
