<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Carrier;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Config\Source\Insurance;
use MondialRelay\Shipping\Model\Label;
use MondialRelay\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use MondialRelay\Shipping\Model\Config\Source\Code;
use MondialRelay\Shipping\Model\Config\Source\Calculation;
use MondialRelay\Shipping\Model\Config\Source\ReturnType;
use Magento\Framework\App\State as AppState;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackingResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory as ResultStatusFactory;
use Magento\Paypal\Model\Express\Checkout as PaypalExpressCheckout;
use Magento\Framework\Module\Manager;
use Psr\Log\LoggerInterface;
use Zend_Measure_Weight;

/**
 * Class MondialRelay
 */
class MondialRelay extends AbstractCarrier implements CarrierInterface
{
    const SHIPPING_CARRIER_CODE = 'mondialrelay';

    const SHIPPING_CARRIER_PICKUP_METHOD = 'mondialrelay_pickup';

    /**
     * @var string $_code
     * @phpcs:disable
     */
    protected $_code = 'mondialrelay';

    /**
     * @var bool $isFixed
     */
    protected $isFixed = true;

    /**
     * @var RateResultFactory $rateResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var RateMethodFactory $rateMethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var TrackingResultFactory $trackFactory
     */
    protected $trackFactory;

    /**
     * @var ResultStatusFactory $trackStatusFactory
     */
    protected $trackStatusFactory;

    /**
     * @var AppState $appState
     */
    protected $appState;

    /**
     * @var ManagerInterface $eventManager
     */
    protected $eventManager;

    /**
     * @var Label $label
     */
    protected $label;

    /**
     * @var Insurance $insurance
     */
    protected $insurance;

    /**
     * @var Manager $moduleManager
     */
    protected $moduleManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param RateResultFactory $rateResultFactory
     * @param RateMethodFactory $rateMethodFactory
     * @param ShippingHelper $shippingHelper
     * @param TrackingResultFactory $trackFactory
     * @param ResultStatusFactory $trackStatusFactory
     * @param ManagerInterface $eventManager
     * @param Label $label
     * @param Insurance $insurance
     * @param Manager $moduleManager
     * @param AppState $appState
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateResultFactory $rateResultFactory,
        RateMethodFactory $rateMethodFactory,
        ShippingHelper $shippingHelper,
        TrackingResultFactory $trackFactory,
        ResultStatusFactory $trackStatusFactory,
        ManagerInterface $eventManager,
        Label $label,
        Insurance $insurance,
        Manager $moduleManager,
        AppState $appState,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->label              = $label;
        $this->insurance          = $insurance;
        $this->rateResultFactory  = $rateResultFactory;
        $this->rateMethodFactory  = $rateMethodFactory;
        $this->shippingHelper     = $shippingHelper;
        $this->trackFactory       = $trackFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->appState           = $appState;
        $this->eventManager       = $eventManager;
        $this->moduleManager      = $moduleManager;
    }

    /**
     * Collect Rates
     *
     * @param RateRequest $request
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($request->getData('is_return')) {
            return $this->addReturnMethod($request);
        }

        $this->addRequestAttributes($request);

        $methods = array_diff(
            array_keys($this->getAllowedMethods()),
            $this->getExcludeMethods($request)
        );

        $quote = $this->getQuote($request);

        if ($quote) {
            $payment = $quote->getPayment();

            $review  = $payment->getAdditionalInformation(PaypalExpressCheckout::PAYMENT_INFO_BUTTON);
            $payerId = $payment->getAdditionalInformation(PaypalExpressCheckout::PAYMENT_INFO_TRANSPORT_PAYER_ID);

            if ($payerId && $review) {
                $methods = array_diff($methods, ['pickup']);
            }

            if ($quote->getIsMultiShipping()) {
                $methods = array_diff($methods, ['pickup']);
            }
        }

        return $this->addMethods($request, $methods);
    }

    /**
     * Add additional attributes to request
     *
     * @param RateRequest $request
     * @return void
     */
    protected function addRequestAttributes(RateRequest $request)
    {
        $size = 0;
        $maxProductSize = 0;
        $maxProductWeight = 0;
        $length = $this->shippingHelper->getLengthAttribute();
        $width  = $this->shippingHelper->getWidthAttribute();
        $height = $this->shippingHelper->getHeightAttribute();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($request->getAllItems() as $item) {
            if ($length && $width && $height) {
                $product = $item->getProduct();
                $totalSize = $product->getData($length) + $product->getData($width) + $product->getData($height);
                $size += $totalSize * $item->getQty();
                if ($totalSize > $maxProductSize) {
                    $maxProductSize = $totalSize;
                }
            }
            if ($item->getWeight() > $maxProductWeight) {
                $maxProductWeight = $item->getWeight();
            }
        }

        $request->setData('package_size', $size);
        $request->setData('package_max_product_size', $maxProductSize);
        $request->setData('package_max_product_weight', $maxProductWeight);
    }

    /**
     * Add Shipping Methods
     *
     * @param RateRequest $request
     * @param array $methodCodes
     * @return Result
     */
    protected function addMethods(RateRequest $request, $methodCodes)
    {
        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        foreach ($methodCodes as $methodCode) {
            /* Check if method is active */
            if (!$this->getConfigData($methodCode . '/active')) {
                continue;
            }

            $countryId = $this->shippingHelper->getCountry($request->getDestCountryId(), $request->getDestPostcode());

            /* Check if country is active */
            $specific = $this->getConfigData($methodCode . '/specificcountry');
            if (!$specific) {
                continue;
            }
            $countries = explode(',', $specific);
            if (!in_array($countryId, $countries)) {
                continue;
            }

            $weightUnit = $this->shippingHelper->getStoreWeightUnit($request->getStoreId());

            /* Check Weight */
            if ($this->shippingHelper->isLimitationActive('weight')) {
                $weightCalculation = $this->shippingHelper->getCalculation('weight');
                $shippingWeight = $request->getPackageWeight();
                if ($weightCalculation == Calculation::MONDIAL_RELAY_CALCULATION_PRODUCT) {
                    $shippingWeight = $request->getData('package_max_product_weight');
                }
                $shippingWeight = $this->shippingHelper->convertWeight(
                    $shippingWeight,
                    $weightUnit,
                    Zend_Measure_Weight::KILOGRAM
                );
                $maxWeight = $this->shippingHelper->getLimit('weight', $shippingWeight, $methodCode, $countryId);
                if ($shippingWeight && $shippingWeight >= $maxWeight) {
                    continue;
                }
            }

            /* Check Size */
            if ($this->shippingHelper->isLimitationActive('size')) {
                $sizeCalculation = $this->shippingHelper->getCalculation('size');
                $shippingSize = $request->getData('package_size');
                if ($sizeCalculation == Calculation::MONDIAL_RELAY_CALCULATION_PRODUCT) {
                    $shippingSize = $request->getData('package_max_product_size');
                }
                $maxSize = $this->shippingHelper->getLimit('size', $shippingSize, $methodCode, $countryId);
                if ($shippingSize && $shippingSize >= $maxSize) {
                    continue;
                }
            }

            /* Price calculation */
            $finalPrice = 0;
            $shippingWeight = $this->shippingHelper->convertWeight(
                $request->getPackageWeight(),
                $weightUnit,
                Zend_Measure_Weight::KILOGRAM
            );

            if ($request->getFreeShipping() !== true) {
                $prices = $this->getPriceData($methodCode, $countryId);
                $total  = $this->getGrandTotal($request);
                foreach ($prices as $price) {
                    $minRange = $shippingWeight >= $price['weight_from'];
                    $maxRange = !$price['weight_to'] || $shippingWeight < $price['weight_to'];

                    if ($minRange && $maxRange) {
                        $finalPrice = $price['price'];
                        if (isset($price['free_from'])) {
                            if ($price['free_from'] && $total >= floatval($price['free_from'])) {
                                $finalPrice = 0;
                            }
                        }
                        break;
                    }
                }

                $finalPrice += $this->getFeeAmount($methodCode, $countryId, $request->getDestPostcode());
            }

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($methodCode);
            $method->setMethodTitle($this->getConfigData($methodCode . '/name'));

            $method->setPrice($finalPrice);
            $method->setCost($finalPrice);

            $this->eventManager->dispatch(
                'mondialrelay_append_method',
                ['method' => $method, 'request' => $request]
            );

            if ($method->getHideMethod()) {
                continue;
            }

            $result->append($method);
        }

        return $result;
    }

    /**
     * Add return method if return requested
     *
     * @param RateRequest $request
     * @return Result
     */
    protected function addReturnMethod(RateRequest $request)
    {
        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle('Mondial Relay');

        $method->setMethod('return');
        $method->setMethodTitle(__('Return'));

        $this->eventManager->dispatch(
            'mondialrelay_append_method',
            ['method' => $method, 'request' => $request]
        );

        $result->append($method);

        return $result;
    }

    /**
     * Retrieve Allowed Methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = [
            'standard' => $this->getConfigData('standard/sort_order'),
            'confort'  => $this->getConfigData('confort/sort_order'),
            'premium'  => $this->getConfigData('premium/sort_order'),
            'pickup'   => $this->getConfigData('pickup/sort_order'),
        ];

        natsort($methods);

        foreach ($methods as $key => $value) {
            $methods[$key] = $this->getConfigData($key . '/name');
        }

        return $methods;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     * @api
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->trackFactory->create();

        $apiConfig = $this->shippingHelper->getApiConfig();

        $apiCompany = $apiConfig['api_company'];
        $apiKey = $apiConfig['api_reference'];

        foreach ($trackings as $tracking) {
            /** @var \Magento\Shipping\Model\Tracking\Result\Status $status */
            $status = $this->trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);

            $params = [
                'ens'      => $apiCompany . $apiKey,
                'exp'      => $tracking,
                'language' => 'FR',
            ];
            $status->setUrl(
                'https://www.mondialrelay.com/public/permanent/tracking.aspx?' . http_build_query($params)
            );

            $result->append($status);
        }

        return $result;
    }

    /**
     * @param DataObject $request
     * @return $this
     */
    public function checkAvailableShipCountries(DataObject $request)
    {
        return $this;
    }

    /**
     * Retrieve price data
     *
     * @param string $method
     * @param string $countryId
     * @return array
     */
    public function getPriceData($method, $countryId)
    {
        $price = $this->getConfigData($method . '/price');
        $final = [];

        $prices = $this->getConfigAsArray($price);

        foreach ($prices as $data) {
            if ($countryId == $data['country']) {
                $final[$data['weight_from'] ? $data['weight_from'] * 1000 : 0] = $data;
            }
        }
        ksort($final);

        return $final;
    }

    /**
     * Retrieve Fee Amount for postcode
     *
     * @param string $method
     * @param string $countryId
     * @param string $postcode
     * @return float
     */
    public function getFeeAmount($method, $countryId, $postcode)
    {
        $fee = $this->getConfigData($method . '/fee');
        $final = 0;

        if (!$postcode) {
            return $final;
        }

        if (!$fee) {
            return $final;
        }

        $fees = $this->getConfigAsArray($fee);

        $postcode = preg_replace("/[^0-9]/", '', $postcode);

        foreach ($fees as $data) {
            if (!$data['postcode']) {
                break;
            }
            $data['postcode'] = trim(preg_replace("/\*/", '(.*)', $data['postcode']));
            if ($countryId == $data['country'] && preg_match('/^' . $data['postcode'] . '$/', $postcode)) {
                $final = floatval($data['fee']);
                break;
            }
        }

        return $final;
    }

    /**
     * Retrieve config as array
     *
     * @param string $data
     * @return array
     */
    public function getConfigAsArray($data)
    {
        try {
            $config = unserialize($data);
        } catch (\Exception $e) {
            $config = [];
        }

        // Since Magento 2.2
        if (empty($config) && json_decode($data)) {
            $config = json_decode($data, true);
        }

        return $config;
    }

    /**
     * Retrieve current cart grand total
     *
     * @param RateRequest $request
     * @return float
     */
    public function getGrandTotal($request)
    {
        $total = 0;
        $items = $request->getAllItems();
        if (isset($items[0])) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $items[0];
            $total = $item->getQuote()->getGrandTotal() - $item->getAddress()->getShippingAmount();
            if ($this->moduleManager->isEnabled('Magento_CustomerBalance')) {
                $total += $item->getAddress()->getData('customer_balance_amount');
            }
        }

        return $total;
    }

    /**
     * Retrieve Exclude methods
     *
     * @param RateRequest $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExcludeMethods($request)
    {
        $exclude = [];

        /* Exclude Premium if all products are not allowed for installation */
        $isPremium = false;

        $items = $request->getAllItems();

        if (!isset($items[0])) {
            return $exclude;
        }

        foreach ($items as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getProduct()->getData('mr_delivery_installation')) {
                $isPremium = true;
            }
        }
        if (!$isPremium) {
            $exclude[] = 'premium';
        }

        return $exclude;
    }

    /**
     * Retrieve current quote
     *
     * @param RateRequest $request
     *
     * @return bool|\Magento\Quote\Model\Quote
     */
    protected function getQuote(RateRequest $request)
    {
        $items = $request->getAllItems();

        if (!isset($items[0])) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $items[0];

        return $item->getQuote();
    }

    /**
     * Return container types of carrier
     *
     * @param DataObject $params
     * @return array
     */
    public function getContainerTypes(DataObject $params = null)
    {
        $levels = $this->insurance->toArray();
        $options = [];

        if ($params) {
            $default = $this->shippingHelper->getInsurance($this->_code, $params->getData('method'));
            $options[$default] = __('Insurance level %1', $default);
        }

        foreach ($levels as $level) {
            if (!isset($options[$level])) {
                $options[$level] = __('Insurance level %1', $level);
            }
        }

        return $options;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array|\Magento\Framework\DataObject
     */
    public function requestToShipment($request)
    {
        return $this->label->doShipmentRequest($request);
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Magento\Framework\DataObject
     */
    public function returnOfShipment($request)
    {
        $rma = $request->getOrderShipment();
        $order = $rma->getOrder();

        $recipient = $this->shippingHelper->getRecipientReturnAddress($order->getStoreId());

        $recipientAddress = [
            'recipient_company'     => $recipient['recipient_company'],
            'recipient_street'      => $recipient['recipient_street'],
            'recipient_postcode'    => $recipient['recipient_postcode'],
            'recipient_city'        => $recipient['recipient_city'],
            'recipient_country'     => $recipient['recipient_country'],
            'recipient_telephone'   => $recipient['recipient_telephone'],
            'recipient_email'       => $recipient['recipient_email'],
            'recipient_pickup'      => null,
            'recipient_return_type' => 'LCC',
        ];

        if ($recipient['recipient_return_type'] == ReturnType::MONDIAL_RELAY_RETURN_TYPE_RELAY) {
            if (!$recipient['recipient_pickup']) {
                $response = new DataObject();
                return $response->setData(
                    ['errors' => __('No return pickup selected in configuration')]
                );
            }

            list($pickupId, $code) = explode('-', $recipient['recipient_pickup']);
            
            $recipientAddress['recipient_pickup']      = $pickupId;
            $recipientAddress['recipient_return_type'] = $code;
        }

        $shipperAddress = $order->getBillingAddress();
        $street  = $shipperAddress->getStreet();

        $request->setData('is_return', true);
        $request->setData('mode_liv', $recipientAddress['recipient_return_type']);
        $request->setData('liv_rel', $recipientAddress['recipient_pickup']);
        $request->setData('shipper_contact_person_first_name', $shipperAddress->getFirstname());
        $request->setData('shipper_contact_person_last_name', $shipperAddress->getLastname());
        $request->setData(
            'shipper_contact_company_name',
            $shipperAddress->getCompany() ?: $shipperAddress->getFirstname() . ' ' . $shipperAddress->getLastname()
        );
        $request->setData('shipper_address_street_1', substr($street[0], 0, 32));
        $request->setData('shipper_address_street_2', '');
        $request->setData('shipper_address_city', $shipperAddress->getCity());
        $request->setData('shipper_address_postal_code', $shipperAddress->getPostcode());
        $request->setData('shipper_address_country_code', $shipperAddress->getCountryId());
        $request->setData('shipper_contact_phone_number', $shipperAddress->getTelephone());
        $request->setData('recipient_contact_person_first_name', $recipientAddress['recipient_company']);
        $request->setData('recipient_contact_person_last_name', '');
        $request->setData('recipient_contact_company_name', $recipientAddress['recipient_company']);
        $request->setData('recipient_contact_phone_number', $recipientAddress['recipient_telephone']);
        $request->setData('recipient_email', $recipientAddress['recipient_email']);
        $request->setData('recipient_address_street_1', $recipientAddress['recipient_street']);
        $request->setData('recipient_address_city', $recipientAddress['recipient_city']);
        $request->setData('recipient_address_postal_code', $recipientAddress['recipient_postcode']);
        $request->setData('recipient_address_country_code', $recipientAddress['recipient_country']);

        return $this->requestToShipment($request);
    }

    /**
     * Check whether girth is allowed for the carrier
     *
     * @param null|string $countyDest
     * @param null|string $carrierMethodCode
     * @return bool
     */
    public function isGirthAllowed($countyDest = null, $carrierMethodCode = null)
    {
        return false;
    }
}
