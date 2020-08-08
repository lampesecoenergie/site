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
 * @package     Ced_RueDuCommerceSdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk\Core;

class Response
{

    const REQUEST_STATUS_SUCCESS = 'success';

    const REQUEST_STATUS_FAILURE = 'failure';

    protected $requestId = null;

    protected $timestamp;

    protected $responseType;

    protected $status = self::REQUEST_STATUS_FAILURE;

    protected $feedFile;

    protected $body;

    protected $error = '';

    public function __construct($params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        if (isset($this->body) and is_array($this->body)) {
            return $this->body;
        }

        return [];
    }

    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setFeedFile($feedFile)
    {
        $this->feedFile = $feedFile;
    }

    public function getFeedFile()
    {
        return $this->feedFile;
    }

    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}
