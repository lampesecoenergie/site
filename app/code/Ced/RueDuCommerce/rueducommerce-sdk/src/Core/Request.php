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
 * @package     RueDuCommerce-Sdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk\Core;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

abstract class Request implements \RueDuCommerceSdk\Core\RequestInterface
{
    /**
     * Debug Logging
     * @var $debugMode
     */
    public $debugMode;

    /**
     * Logger
     * @var $logger
     */
    public $logger;

    /**
     * Api Base Url
     * @var string $apiUrl
     */
    public $apiUrl;

    /**
     * Api Auth Key
     * @var string $apiAuthKey
     */
    public $apiAuthKey;

    /**
     * XML Parser
     * @var \RueDuCommerceSdk\Core\Generator
     */
    public $xml;

    /**
     * Parser
     * @var \RueDuCommerceSdk\Core\Parser
     */
    public $parser;

    /**
     * Base Directory
     * @var string
     */
    public $baseDirectory;

    /**
     * Xsd files path
     * @var string
     */
    public $xsdPath;

    /**
     * Xsd files directory
     * @var string
     */
    public $xsdDir;

    /**
     * Request constructor.
     * @param ConfigInterface $config
     */
    public function __construct(\RueDuCommerceSdk\Core\ConfigInterface $config)
    {
        $this->baseDirectory = $config->getBaseDirectory();
        $this->debugMode = $config->getDebugMode();
        $this->xml = $config->getGenerator();
        $this->parser = $config->getParser();
        $this->logger = $config->getLogger();
        $this->apiAuthKey = $config->getApiKey();
        $this->apiUrl = $config->getApiUrl();
    }

    /**
     * Put Request
     * $params = ['file' => "", 'data' => "" ]
     * @param string $url
     * @param array $params
     * @return string
     */
    public function putRequest($url, $params = array())
    {
        $request = null;
        $response = null;
        try {
            $body = '';
            if (isset($params['file'])) {
                $body = fopen($params['file'], 'r');
            } elseif (isset($params['data'])) {
                $body = $params['data'];
            } elseif (isset($params['requests_xml'])) {
                $body = $params;
            }
            $flag = preg_match("/^(merchant)/", $url);
            if ($flag) {
                $url = \RueDuCommerceSdk\Core\RequestInterface::ORDERS_API_URL . $url . '/' . $this->apiAuthKey;
            } else {
                $url= $this->apiUrl.$url;
            }
            $headers = array(
                'Authorization: ' . $this->apiAuthKey,
                'Accept: application/xml',
                'Content-Type: application/xml',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            //$header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $servererror = curl_error($ch);

            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }

            curl_close($ch);
            return $body;

        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Api\\putRequest() : \n URL: " . $url .
                    "\n Request : \n" . var_export($request, true) .
                    "\n Response : \n " . var_export($response, true) .
                    "\n Errors : \n " . var_export($e->getMessage(), true)
                );
            }
            return false;
        }
    }

    /**
     * Post Request
     * $params = ['file' => "", 'data' => "" ]
     * @param string $url
     * @param array $params
     * @return string
     */
    public function postRequest($url, $params = array(), $uploadType = NULL)
    {
        $request = null;
        $response = null;
        try {
            $body = '';
            $cFile = '';
            if (isset($params['file'])) {
                if (function_exists('curl_file_create')) {
                    $cFile = curl_file_create($params['file']);
                } else {
                    $cFile = '@' . realpath($params['file']);
                }
            } elseif (isset($params['data'])) {
                $body = $params['data'];
            }
            $flag = preg_match("/^(merchant)/", $url);
            if ($flag) {
                $url = \RueDuCommerceSdk\Core\RequestInterface::ORDERS_API_URL . $url . '/' . $this->apiAuthKey;
            } else {
                $url= $this->apiUrl.$url;
            }

            if($uploadType == "offer") {
                $withProducts = ($params['with_products'] == 'true') ? 'true' : 'false';
                $body = array('file' => $cFile, 'import_mode' => 'NORMAL', 'with_products' => $withProducts);
            } elseif ($uploadType == "shipment" && isset($params['requests_xml'])) {
                $body = $params;
            }
            else {
                $body = array('file' => $cFile);
            }

            $headers = array(
                'Authorization: ' . $this->apiAuthKey,
                'Accept: application/xml',
                'Content-Type: multipart/form-data',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            //$header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Api\\postRequest() : \n URL: " . $url .
                    "\n Request : \n" . var_export($request, true) .
                    "\n Response : \n " . var_export($response, true) .
                    "\n Errors : \n " . var_export($e->getMessage(), true)
                );
            }
            return false;
        }
    }

    /**
     * @param $xml
     * @return array
     * @throws \Exception
     */
    public function xmlToArray($xml)
    {
        return $this->parser->loadXML($xml)->xmlToArray();
    }

    /**
     * Response Parse and Save to db
     * @param bool $response
     * @param string $type
     * @param string $filePath
     * @return array
     */
    public function responseParse($response = false, $type = self::FEED_CODE_ITEM_UPDATE, $filePath = '')
    {
        $feedResponseData = array();
        $feedResponse = array(
            'feed_id' => null,
            'feed_date' => date('Y-m-d H:i:s'),
            'feed_type' => $type,
            'feed_status' => 'RueDuCommerce api failed. Kindly check the api logs.',
            'feed_errors' => "{\"Api Failure\":\"RueDuCommerce api failed. Kindly check the api logs.\"}",
            'feed_file' => $filePath
        );

        if ($type && $response) {
            try {
                $data = $this->parser->loadXML($response)->xmlToArray();

                reset($data); // make sure array pointer is at first element

                $firstKey = key($data);
                if (isset($data[$firstKey]['product_import_id'])) {
                    if (isset($data[$firstKey]['product_import_id'])) {
                        $feedResponse['feed_id'] = $data[$firstKey]['product_import_id'];
                        $feedResponse['feed_type'] = 'item-update';
                        //Check for report details
                        $feed = $this->getFeeds($data[$firstKey]['product_import_id']);

                        if (isset($feed['error']) && !empty($feed['error'])) {
                            $feedResponse['feed_errors'] = $feed['error'];
                        } else {
                            $feedResponse['feed_errors'] =
                                "{\"Submitted\":\"Feed submitted successfully. Kindly check the feed details.\"}";
                        }
                        //Check for document status
                        $feedResponse['feed_status'] = isset($feed['processing-report']['_value']['status']) ?
                            $feed['processing-report']['_value']['status'] : "Submitted";
                    } else if ($firstKey == 'error') {
                        $feedResponse['feed_status'] = "failure";
                        $feedResponse['feed_errors'] = $data['error']['message'];
                    } else {
                        $feedResponse['feed_status'] = "failure";
                        $feedResponse['feed_errors'] = '';
                    }
                    array_push($feedResponseData, $feedResponse);
                }

                if (isset($data[$firstKey]['import_id'])) {

                    $feedResponse['feed_id'] = $data[$firstKey]['import_id'];
                    $feedResponse['feed_type'] = $type;
                    //Check for report details
                    $feed = $this->getFeeds($data[$firstKey]['import_id']);

                    if (isset($feed['error']) && !empty($feed['error'])) {
                        $feedResponse['feed_errors'] = $feed['error'];
                    } else {
                        $feedResponse['feed_errors'] =
                            "{\"Submitted\":\"Feed submitted successfully. Kindly check the feed details.\"}";
                    }
                    //Check for document status
                    $feedResponse['feed_status'] = isset($feed['processing-report']['_value']['status']) ?
                        $feed['processing-report']['_value']['status'] : "Submitted";
                    array_push($feedResponseData, $feedResponse);
                } else if($firstKey=='error'){
                    $feedResponse['feed_status'] = "failure";
                    $feedResponse['feed_errors'] = $data['error']['message'];
                    array_push($feedResponseData, $feedResponse);
                } else {
                    $feedResponse['feed_status'] = "failure";
                    $feedResponse['feed_errors'] = '';
                    array_push($feedResponseData, $feedResponse);
                }
            } catch (\Exception $e) {
                if ($this->debugMode) {
                    $this->logger->debug(
                        "RueDuCommerceSdk\\Core\\Request\\responseParse(): \n Response : \n" . var_export($response, true) .
                        "\n Exception : \n" . var_export($e->getMessage(), true)
                    );
                }
            }
        }
        return $feedResponseData;
    }

    /**
     * Get Feeds, Get Single Feed, Get Single Feed with Error Details
     * @param null $feedId
     * @param string $subUrl
     * @return array|boolean
     */
    public function getFeeds($feedId = null, $subUrl = self::GET_FEEDS_SUB_URL)
    {
        $response = null;
        try {
            $subUrl = sprintf($subUrl, $feedId);

            $response = $this->getRequest($subUrl);
            $parseXMl = $this->parser->loadXML($response)->xmlToArray();
            if($parseXMl == '') {
                $response = explode(';', $response);
            } else {
                $response = $parseXMl;
            }
            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    sprintf(
                        "RueDuCommerceSdk\\Core\\Request\\getFeeds(): \n Response : \n %s \n Exception : %s \n",
                        var_export($response, true),
                        var_export($e->getMessage(), true)
                    )
                );
            }
            return false;
        }
    }

    /**
     * Get Request
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    /**
     * Get Request
     * @param string $url
     * @param string|[] $params
     * @return string
     * @errors 1. <api-response xmlns="http://seller.marketplace.catch.com/shared/v1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://seller.marketplace.catch.com/shared/v1 https://seller.marketplace.catch.com/SellerPortal/s/schema/shared/api-response-v1.xsd">
     * <error-detail>Request with future timestamp is not allowed. The timestamp provided was 2017-07-03T15:50:32Z</error-detail>
     * </api-response>
     */
    public function getRequest($url, $params = array())
    {
        $request = null;
        $response = null;
        try {
            $headers = array(
                'Authorization: ' . $this->apiAuthKey,
                'Accept: application/xml',
                'Content-Type: application/xml',
            );
            $flag = preg_match("/^(merchant)/", $url);
            if ($flag) {
                $url = \RueDuCommerceSdk\Core\RequestInterface::ORDERS_API_URL . $url . '_' . $this->apiAuthKey . '.xml';
            } else {
                $url = $this->apiUrl.$url;
            }
            if(count($params))
                $url = $url.'?'.http_build_query($params);

            $request = curl_init();
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($request, CURLOPT_URL, $url);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($request);

            $errors = curl_error($request);
            if (!empty($errors)) {
                curl_close($request);
                throw new \Exception($errors);
            }
            curl_close($request);
            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Api\\getRequest() : \n URL: " . $url .
                    "\n Request : \n" . var_export($request, true) .
                    "\n Response : \n " . var_export($response, true) .
                    "\n Errors : \n " . var_export($e->getMessage(), true)
                );
            }
            return false;
        }
    }


    /**
     * Get a File or Create
     * @param $path
     * @param null $name
     * @return string
     */
    public function getFile($path, $name = null)
    {

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        if ($name != null) {
            $path = $path . DS . $name;

            if (!file_exists($path)) {
                @file($path);
            }
        }

        return $path;
    }

    /**
     * Validate XML
     * @param $filePath
     * @param $xsdPath
     * @param string $type
     * @return array
     */
    public function validateXml($filePath, $xsdPath, $type = 'lmp-item')
    {
        $validation = [
            'feed_id' => null,
            'feed_date' => date('Y-m-d H:i:s'),
            'feed_type' => $type,
            'feed_status' => 'Xml validation failed',
            'feed_errors' => "{\"XML Validation Failure\": \"Unable to load the file for validation\"}",
            'feed_file' => $filePath
        ];

        libxml_use_internal_errors(true);
        $feed = new \DOMDocument();
        $feed->preserveWhitespace = false;
        $result = $feed->load($filePath);
        if ($result === true) {
            if (($feed->schemaValidate($xsdPath))) {
                $validation['feed_errors'] = false;
                $validation['feed_status'] = 'Xml validation successfull';
            } else {
                $errors = libxml_get_errors();
                $errorList = [];
                foreach ($errors as $error) {
                    $errorList[] = 'Error : ' . $error->message;
                }
                if ($this->debugMode) {
                    $this->logger->debug(
                        "RueDuCommerceSdk\\Core\\Request\\validateXml(): \n" . "\n Errors : \n" . var_export($errorList, true)
                    );
                }
                $validation['feed_errors'] = json_encode($errorList, JSON_PRETTY_PRINT);
                $validation['feed_status'] = 'Xml validation failed';
            }
        }
        return $validation;
    }
}
