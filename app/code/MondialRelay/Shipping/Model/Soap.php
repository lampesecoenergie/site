<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use MondialRelay\Shipping\Helper\data as ShippingHelper;
use Psr\Log\LoggerInterface;
use SoapFault;
use SoapClient;
use Exception;

/**
 * Class Soap
 */
class Soap
{
    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var SoapClient $client
     */
    protected $client = null;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var int $storeId
     */
    protected $storeId = null;

    /**
     * @var int $websiteId
     */
    protected $websiteId = null;

    /**
     * @param ShippingHelper $shippingHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShippingHelper $shippingHelper,
        LoggerInterface $logger
    ) {
        $this->shippingHelper = $shippingHelper;
        $this->logger         = $logger;
    }

    /**
     * Store setter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * Website setter
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;

        return $this;
    }

    /**
     * Execute WS request
     *
     * @param string $method
     * @param array $data
     * @return array
     */
    public function execute($method, $data)
    {
        $result = [
            'error'    => false,
            'response' => false,
        ];

        try {
            $data = array_merge(['Enseigne' => $this->getApiCompany()], $data);
            $security = strtoupper(md5(implode('', $data) . $this->getApiKey()));
            $data['Security'] = $security;

            $response = $method . 'Result';
            $request = $this->getClient()->$method($data)->$response;

            if ($request->STAT == "0") {
                $result['response'] = $request;
            } else {
                $result['error'] = __('%1 in error with code %2', $method, $request->STAT);
            }
        } catch (SoapFault $fault) {
            $result['error'] = $fault->getMessage();
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        if ($result['error']) {
            $this->logger->error($result['error']);
        }

        return $result;
    }

    /**
     * Retrieve SOAP Client
     *
     * @return SoapClient
     */
    protected function getClient()
    {
        if (is_null($this->client)) {
            if ($this->getApiWsdl()) {
                $this->client = new SoapClient($this->getApiWsdl(), ['exceptions' => true, 'trace' => false]);
            }
        }

        return $this->client;
    }

    /**
     * Retrieve WSDL Link
     *
     * @return string
     */
    protected function getApiWsdl()
    {
        $config = $this->getApiConfig();

        return $config['wsdl'];
    }

    /**
     * Retrieve API Key
     *
     * @return string
     */
    protected function getApiKey()
    {
        $config = $this->getApiConfig();

        return $config['api_key'];
    }

    /**
     * Retrieve API company
     *
     * @return string
     */
    protected function getApiCompany()
    {
        $config = $this->getApiConfig();

        return $config['api_company'];
    }

    /**
     * Retrieve API Config
     *
     * @return array
     */
    protected function getApiConfig()
    {
        return $this->shippingHelper->getApiConfig($this->storeId, $this->websiteId);
    }
}
