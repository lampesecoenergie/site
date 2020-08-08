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
 * @category    RueDuCommerceSdk-Sdk
 * @package     Ced_RueDuCommerceSdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk\Core;

interface ConfigInterface
{

    /**
     * ConfigInterface constructor.
     * @param array $params
     */
    public function __construct($params = []);

    /**
     * Set Base Directory
     * @param $baseDirectory
     * @return mixed
     */
    public function setBaseDirectory($baseDirectory);

    /**
     * Get Base Directory
     * @return mixed
     */
    public function getBaseDirectory();

    /**
     * Set RueDuCommerce ApiKey
     * @param string $apiKey
     * @return boolean
     */
    public function setApiKey($apiKey);

    /**
     * Get RueDuCommerce ApiKey
     * @return mixed
     */
    public function getApiKey();

    /**
     * Set RueDuCommerce Service Url
     * @param string $apiUrl
     * @return boolean
     */
    public function setApiUrl($apiUrl);

    /**
     * Get RueDuCommerce Service Url
     * @return string
     */
    public function getApiUrl();

    /**
     * Set to enable or disable logging
     * @param bool $debugMode
     * @return boolean
     */
    public function setDebugMode($debugMode = true);

    /**
     * Get Logging status
     * @return boolean
     */
    public function getDebugMode();

    /**
     * Get Xml Generator
     * @return mixed
     */
    public function getParser();

    /**
     * Set Xml Parser
     * @param $parser
     * @return mixed
     */
    public function setParser($parser);

    /**
     * Get Xml Generator
     * @return mixed
     */
    public function getGenerator();

    /**
     * Set Xml Generator
     * @param $generator
     * @return mixed
     */
    public function setGenerator($generator);

    /**
     * Get Logger
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();

    /**
     * Set Logger
     * @param \Psr\Log\LoggerInterface $logger
     * @return mixed
     */
    public function setLogger($logger);
}
