<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Helper;

use MondialRelay\Shipping\Model\Config\Source\Code;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend_Measure_Weight;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const CONFIG_PREFIX = 'carriers/mondialrelay/';

    const RETURN_CONFIG_PREFIX = 'return/recipient_';

    const RETURN_FILE_PATH = 'mondialrelay/return';

    const RETURN_FILE_PREFIX = 'RETURN_';

    /**
     * @var RegionFactory $regionFactory
     */
    protected $regionFactory;

    /**
     * @var Filesystem $fileSystem
     */
    protected $fileSystem;

    /**
     * @var DirectoryList $directoryList
     */
    protected $directoryList;

    /**
     * @var Code $code
     */
    protected $code;

    /**
     * @var array $filters
     */
    protected $filters = ['weight', 'size'];

    /**
     * @param Context $context
     * @param RegionFactory $regionFactory
     * @param Filesystem $fileSystem
     * @param DirectoryList $directoryList
     * @param Code $code
     */
    public function __construct(
        Context $context,
        RegionFactory $regionFactory,
        Filesystem $fileSystem,
        DirectoryList $directoryList,
        Code $code
    ) {
        parent::__construct($context);

        $this->regionFactory  = $regionFactory;
        $this->fileSystem     = $fileSystem;
        $this->directoryList  = $directoryList;
        $this->code           = $code;
    }

    /**
     * Retrieve configuration value
     *
     * @param string $path
     * @return array|string|bool
     */
    public function getConfig($path)
    {
        $config = [
            'standard' => [
                'code'      => 'HOM',
                'countries' => ['FR', 'BE', 'LU', 'ES', 'DE', 'GB', 'IT', 'AT', 'PT', 'NL']
            ],
            'confort'  => [
                'code'      => 'LD1',
                'countries' => ['FR', 'BE', 'LU', 'ES']
            ],
            'premium'  => [
                'code'      => 'LDS',
                'countries' => ['FR', 'BE', 'LU', 'ES']
            ],
            'pickup'   => [
                'countries'  => ['FR', 'BE', 'LU', 'ES', 'DE', 'AT'],
                'limits' => [
                    Code::MONDIAL_RELAY_CODE_DRI => ['weight' => 150, 'size' => 650],
                    Code::MONDIAL_RELAY_CODE_24L => ['weight' => 50,  'size' => 200],
                    Code::MONDIAL_RELAY_CODE_24R => ['weight' => 30,  'size' => 150],
                ],
                'groups' => [
                    Code::MONDIAL_RELAY_CODE_DRI => ['label' => __('Drive'),  'group' => 'drive'],
                    Code::MONDIAL_RELAY_CODE_24L => ['label' => __('Pickup'), 'group' => 'pickup'],
                    Code::MONDIAL_RELAY_CODE_24R => ['label' => __('Pickup'), 'group' => 'pickup'],
                ],
            ],
        ];

        $keys = explode('/', $path);

        $skip = false;
        foreach ($keys as $i => $key) {
            if ($skip) {
                $skip = false;
                continue;
            }
            if (isset($config[$key])) {
                $config = $config[$key];
                if (is_string($config)) {
                    if (preg_match('/^::/', $config)) {
                        $method = preg_replace('/^::/', '', $config);
                        $config = $this->$method();
                        $skip = true;
                    }
                }
            } else {
                $config = false;
                break;
            }
        }

        return $config;
    }

    /**
     * Retrieve country
     *
     * @param string $countryId
     * @param string $postcode
     * @return string
     */
    public function getCountry($countryId, $postcode = null)
    {
        if ($countryId == 'MC') { // Monaco
            $countryId = 'FR';
        }

        if ($countryId == 'AD') { // Andorre
            $countryId = 'FR';
        }

        if ($postcode) {
            if ($countryId == 'FR') {
                $countryId = $this->getDomTomCountry($postcode);
            }
        }

        return $countryId;
    }

    /**
     * Retrieve Dom Tom Country with postcode
     *
     * @param string $postcode
     * @return string
     */
    public function getDomTomCountry($postcode)
    {
        $countryId = 'FR';
        $postcodes = $this->getDomTomPostcodes();

        $postcode = preg_replace('/\s+/', '', $postcode);
        foreach ($postcodes as $code => $regex) {
            if (preg_match($regex, $postcode)) {
                $countryId = $code;
                break;
            }
        }
        return $countryId;
    }

    /**
     * Retrieve Dom-Tom countries code ISO-2
     *
     * @return array
     */
    public function getDomTomCountries()
    {
        return [
            'GP', // Guadeloupe
            'MQ', // Martinique
            'GF', // Guyane
            'RE', // La réunion
            'PM', // St-Pierre-et-Miquelon
            'YT', // Mayotte
            'TF', // Terres-Australes
            'WF', // Wallis-et-Futuna
            'PF', // Polynésie Française
            'NC', // Nouvelle-Calédonie
            'BL', // Saint-Barthélemy
            'MF', // Saint-Martin (partie française)
        ];
    }

    /**
     * Retrieve Dom-Tom postcodes
     *
     * @return array
     */
    public function getDomTomPostcodes()
    {
        return [
            'BL' => '/^97133$/', // Saint-Barthélemy
            'MF' => '/^97150$/', // Saint-Martin (partie française)
            'GP' => '/^971[0-9]{2}$/', // Guadeloupe
            'MQ' => '/^972[0-9]{2}$/', // Martinique
            'GF' => '/^973[0-9]{2}$/', // Guyane
            'RE' => '/^974[0-9]{2}$/', // La réunion
            'PM' => '/^975[0-9]{2}$/', // St-Pierre-et-Miquelon
            'YT' => '/^976[0-9]{2}$/', // Mayotte
            'TF' => '/^984[0-9]{2}$/', // Terres-Australes
            'WF' => '/^986[0-9]{2}$/', // Wallis-et-Futuna
            'PF' => '/^987[0-9]{2}$/', // Polynésie Française
            'NC' => '/^988[0-9]{2}$/', // Nouvelle-Calédonie
        ];
    }

    /**
     * Retrieve region
     *
     * @param string $countryId
     * @param string $postcode
     * @return \Magento\Directory\Model\Region
     */
    public function getRegion($countryId, $postcode)
    {
        $code = (int)substr($postcode, 0, 2);
        if ($code == 20) {
            $code = (int)$postcode >= 20200 ? '2B' : '2A';
        }
        if ($this->getDomTomCountry($postcode) != 'FR') {
            $countryId = 'FR';
            $code = 'OM';
        }
        $instance = $this->regionFactory->create();

        return $instance->loadByCode($code, $countryId);
    }

    /**
     * Retrieve API configuration
     *
     * @param int $storeId
     * @param int $websiteId
     * @return array
     */
    public function getApiConfig($storeId = null, $websiteId = null)
    {
        $scopeType = ScopeInterface::SCOPE_STORE;
        $scopeCode = $storeId;
        if (!$storeId && $websiteId) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $websiteId;
        }

        $prefix = self::CONFIG_PREFIX;

        return [
            'wsdl'          => $this->getRelayDomain() . '/WebService/Web_Services.asmx?WSDL',
            'api_company'   => $this->scopeConfig->getValue($prefix . 'api_company', $scopeType, $scopeCode),
            'api_key'       => $this->scopeConfig->getValue($prefix . 'api_key', $scopeType, $scopeCode),
            'api_reference' => $this->scopeConfig->getValue($prefix . 'api_reference', $scopeType, $scopeCode),
        ];
    }

    /**
     * Retrieve Mondial Relay domain
     *
     * @return string
     */
    public function getRelayDomain()
    {
        return 'https://www.mondialrelay.fr';
    }

    /**
     * Retrieve configured codes
     *
     * @param int $storeId
     * @return string
     */
    public function getPickupCodes($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'pickup/codes',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve all selected codes
     *
     * @param int $storeId
     * @return array
     */
    public function getPickupAvailableCodes($storeId = null)
    {
        $config = $this->getPickupCodes($storeId);

        $available = [];
        if (!$config) {
            $available[] = Code::MONDIAL_RELAY_CODE_24R;
        } else {
            $available = explode(',', $config);
        }

        $codes = $this->code->toArray();

        $result = [];
        foreach ($available as $code) {
            if (isset($codes[$code])) {
                $result[$code] = $codes[$code];
            }
        }

        return $result;
    }

    /**
     * Retrieve available pickup codes by weight and size
     *
     * @param array $filters
     * @param int $storeId
     * @return array
     */
    public function filterPickupCodes($filters, $storeId = null)
    {
        $available = $this->getPickupAvailableCodes();

        $limits = $this->getConfig('pickup/limits');

        $group  = [];
        foreach ($limits as $code => $limit) {
            if (!isset($available[$code])) {
                continue;
            }

            $isValid = true;
            foreach ($filters as $filter => $value) {
                if (in_array($filter, $this->filters) &&
                    $this->isLimitationActive($filter) &&
                    $value > $limit[$filter]) {
                    $isValid = false;
                }
            }
            if ($isValid) {
                $groups = $this->getConfig('pickup/groups/' . $code);
                $group[$groups['group']] = [$code => $groups['label']];
            }
        }

        $final = [];
        foreach ($group as $data) {
            $final = array_merge($final, $data);
        }

        return array_reverse($final);
    }

    /**
     * Retrieve method max weight
     *
     * @param string $type
     * @param float $value
     * @param string $method
     * @param string $countryId
     * @param int $storeId
     * @return float
     */
    public function getLimit($type, $value, $method, $countryId, $storeId = null)
    {
        $limit = $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . $method . '/max_' . $type,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($method !== 'pickup') {
            return $limit;
        }
        if ($countryId !== 'FR') {
            return $limit;
        }

        $available = $this->filterPickupCodes([$type => $value], $storeId);
        $limit = 0;
        foreach ($available as $code => $label) {
            if ($this->getConfig('pickup/limits/' . $code . '/' . $type) > $limit) {
                $limit = $this->getConfig('pickup/limits/' . $code . '/' . $type);
            }
        }

        return $limit;
    }

    /**
     * Retrieve if debug mode is active
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PREFIX . 'limitation/debug',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Weight limitation activation
     *
     * @param string $type
     * @return bool
     */
    public function isLimitationActive($type)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PREFIX . 'limitation/' . $type . '_limitation',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Weight Calculation
     *
     * @param string $type
     * @return string
     */
    public function getCalculation($type)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'limitation/' . $type . '_calculation',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Length Attribute
     *
     * @return string
     */
    public function getLengthAttribute()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'limitation/length_attribute');
    }

    /**
     * Retrieve Width Attribute
     *
     * @return string
     */
    public function getWidthAttribute()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'limitation/width_attribute');
    }

    /**
     * Retrieve Height Attribute
     *
     * @return string
     */
    public function getHeightAttribute()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'limitation/height_attribute');
    }

    /**
     * Retrieve default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve pickup result number
     *
     * @return string
     */
    public function getResultNumber()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'pickup/number',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve default address
     *
     * @return array
     */
    public function getDefaultAddress()
    {
        $address = [
            'postcode'   => '',
            'country_id' => $this->getDefaultCountry(),
        ];

        if ($this->scopeConfig->isSetFlag(self::CONFIG_PREFIX . 'pickup/apply_default', ScopeInterface::SCOPE_STORE)) {
            $address['postcode'] = $this->scopeConfig->getValue(
                self::CONFIG_PREFIX . 'pickup/default_postcode',
                ScopeInterface::SCOPE_STORE
            );
            $address['country_id'] = $this->scopeConfig->getValue(
                self::CONFIG_PREFIX . 'pickup/default_country',
                ScopeInterface::SCOPE_STORE
            );
        }

        return $address;
    }

    /**
     * Convert weight in gram to config weight unit
     *
     * @param float $weight
     * @param string $from
     * @param string $to
     * @return float
     */
    public function convertWeight($weight, $from, $to)
    {
        $kgs = ['kgs', Zend_Measure_Weight::KILOGRAM];
        $lbs = ['lbs', Zend_Measure_Weight::POUND];

        if (in_array($from, $kgs) && in_array($to, $lbs)) {
            $weight = $weight * 2.20462;
        }

        if (in_array($from, $lbs) && in_array($to, $kgs)) {
            $weight = $weight / 2.20462;
        }

        if ($weight < 0.01) {
            $weight = 0.01;
        }

        return $weight;
    }

    /**
     * Retrieve store weight unit
     *
     * @param int $storeId
     * @param bool $fullName
     * @return string
     */
    public function getStoreWeightUnit($storeId = null, $fullName = false)
    {
        $value = $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($fullName) {
            if ($value == 'kgs') {
                $value = Zend_Measure_Weight::KILOGRAM;
            }
            if ($value == 'lbs') {
                $value = Zend_Measure_Weight::POUND;
            }
        }

        return $value;
    }

    /**
     * Retrieve default insurance
     *
     * @param string $code
     * @param string $method
     * @return string
     */
    public function getInsurance($code, $method)
    {
        if (empty($code) && empty($method)) {
            return false;
        }

        return intval($this->scopeConfig->getValue('carriers/' . $code . '/' . $method . '/insurance'));
    }

    /**
     * Retrieve Label size
     *
     * @return string
     */
    public function getLabelSize()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'label/size');
    }

    /**
     * Retrieve packaging weight
     *
     * @return float
     */
    public function getPackagingWeight()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'label/packaging_weight');
    }

    /**
     * Retrieve number of day for label deletion in database
     *
     * @return int
     */
    public function getDeleteLabelAfter()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'label/delete_after');
    }

    /**
     * Retrieve Shipping Status
     *
     * @return int
     */
    public function getShippingStatus()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PREFIX . 'label/shipping_status');
    }

    /**
     * Retrieve Shipping Name
     *
     * @param int $storeId
     * @return int
     */
    public function getShipperName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'label/shipper_name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Recipient Return Address
     *
     * @param int $storeId
     * @return array
     */
    public function getRecipientReturnAddress($storeId = null)
    {
        return [
            'recipient_return_type' => $this->getRecipientReturnConfig('return_type', $storeId),
            'recipient_company'     => $this->getRecipientReturnConfig('company', $storeId),
            'recipient_email'       => $this->getRecipientReturnConfig('email', $storeId),
            'recipient_telephone'   => $this->getRecipientReturnConfig('telephone', $storeId),
            'recipient_street'      => $this->getRecipientReturnConfig('street', $storeId),
            'recipient_city'        => $this->getRecipientReturnConfig('city', $storeId),
            'recipient_postcode'    => $this->getRecipientReturnConfig('postcode', $storeId),
            'recipient_country'     => $this->getRecipientReturnConfig('country', $storeId),
            'recipient_pickup'      => $this->getRecipientReturnConfig('pickup', $storeId),
        ];
    }

    /**
     * Retrieve Return Config Value
     *
     * @param string $path
     * @param int $storeId
     * @return string
     */
    public function getRecipientReturnConfig($path, $storeId = null)
    {
        $prefix = self::CONFIG_PREFIX . self::RETURN_CONFIG_PREFIX;

        return $this->scopeConfig->getValue($prefix . $path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Retrieve return label path
     *
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $full
     * @param bool $url
     * @return string
     */
    public function getReturnLabelPath($order, $full = false, $url = false)
    {
        $code = sha1($order->getCreatedAt() . $order->getIncrementId() . $order->getId());
        $path = self::RETURN_FILE_PATH . '/' . self::RETURN_FILE_PREFIX . $code . '.pdf';

        if (!$full) {
            return $path;
        }

        if ($url) {
            return $this->_urlBuilder->getDirectUrl(
                $this->fileSystem->getUri(DirectoryList::MEDIA) . '/' . $path,
                ['_nosid' => true]
            );
        }

        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Write Mondial Relay Return Label
     *
     * @param string $fileContent
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    public function writeReturnLabel($fileContent, $order)
    {
        $name = $this->getReturnLabelPath($order);

        $writer = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $file = $writer->openFile($name, 'w');

        try {
            $file->lock();
            try {
                $file->write($fileContent);
            } finally {
                $file->unlock();
            }
        } finally {
            $file->close();
        }

        return true;
    }

    /**
     * Retrieve pixel size for new PDF page
     *
     * @return string
     */
    public function getPdfPageSize()
    {
        $size = $this->getLabelSize();

        $sizes = [
            'URL_PDF_A4'    => '595:842:',
            'URL_PDF_A5'    => '595:419:',
            'URL_PDF_10x15' => '283:425:',
        ];

        if (!isset($sizes[$size])) {
            return '283:425:';
        }

        return $sizes[$size];
    }

    /**
     * Retrieve Company Name
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->scopeConfig->getValue('general/store_information/name');
    }
}
