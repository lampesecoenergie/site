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
 * @category    Ced
 * @package     Ced_2.2.5
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Helper\Api;


class Request extends \Magento\Framework\App\Helper\AbstractHelper
{

    const APP_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VyX2lkIjoiMSIsInJvbGUiOiJhcHAiLCJpYXQiOjE1MzQ1MTE5NDYsImlzcyI6Imh0dHBzOlwvXC9hcHBzLmNlZGNvbW1lcmNlLmNvbSIsImF1ZCI6ImV4YW1wbGUuY29tIiwibmJmIjoxNTM0NTExOTQ2LCJ0b2tlbl9pZCI6MTUzNDUxMTk0Nn0.WfVPrYJfhf6M6vkXR5qvM8kdkqbFvpozkIyjv8mf-wCvpeo8ZWNtnvSFzGR5aQ5C2EvJA-lMQGv6pLoxxGL2PXzFa3UF_buV7uKrl4Z9a_R-dTcIkmEQbEt2NKTUXeXgM4Exzr6DZpRCVImsZDnW9jfrRUjf1Ygao0BXWk6_OmWxTxetQvDoW8B2MV35EC_afh4TH0LCmy8M8RkwfUM6D39MKkVeETr3zogy-tY_uBPb6A_Z0VC9LnAOCriSF-OnYi_v_uNISr7OYmXB0UHThY8BYWjvtdDoyFXtEeRlh4KTCttOKB2lVXalMnQP5GqnJO6VVXiN4NXkxTgnSjp6Gw";

    const BASE_URL = "https://sellernext.com/api/";

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curlClient;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    public $configResourceModel;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * @var \Ced\Integrator\Helper\Config
     */
    public $helperConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Ced\Integrator\Helper\Config $helperConfig
    )
    {
        $this->curlClient = $curlClient;
        $this->configResourceModel = $config;
        $this->helperConfig = $helperConfig;
        $this->scopeConfigManager = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Send Curl Request to Server
     * @param array $params
     * @param string $method
     * @param string $type
     * @param string $module
     * @return mixed
     */

    public function send($params = [], $method, $type = 'GET', $module)
    {
        $response = [];
        try {
            $token = $this->helperConfig->getApiToken();
            if ($token) {
                $requestParams = $params;
                $requestParams['bearer'] = $token;
                $url = self::BASE_URL . $module . '/' . $method;

                $this->curlClient->setOption(CURLOPT_SSL_VERIFYHOST, false);
                $this->curlClient->setOption(CURLOPT_SSL_VERIFYPEER, false);
                if ($type == 'POST') {
                    $this->curlClient->post($url, $requestParams);
                } else {
                    if (!empty($requestParams)) {
                        $url = $url . '?' . http_build_query($requestParams);
                    }
                    $this->curlClient->get($url);
                }
                $response = $this->curlClient->getBody();
            } else {
                $response['success'] = false;
                $response['message'] = "Please authorize {$module} module with CedCommerce App";
            }
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}