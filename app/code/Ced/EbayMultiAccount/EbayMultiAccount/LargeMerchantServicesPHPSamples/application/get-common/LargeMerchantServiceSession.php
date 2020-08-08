<?php require_once('ServiceEndpointsAndTokens.php') ?>
<?php require_once('BulkDataExchangeRequest.php') ?>
<?php require_once('FileTransferServiceUploadRequest.php') ?>
<?php require_once('FileTransferServiceDownloadRequest.php') ?>
<?php

/**
 * Session for sending API requests to a service supported
 * by the Large Merchant Services Platform.
 * 
 * As of the writing of this documentation this includes:
 * (1) Bulk Data Exchange Service (BDX)
 * (2) File Transfer Service (FTS) 
 */
class LargeMerchantServiceSession {
	
	private $dataFormat;
	private $responseDataFormat;
	private $environment;
	private $securityToken;
	
	public function __construct($dataFormat, $responseDataFormat, $environment, $token)
	{
		$this->dataFormat = $dataFormat;
		$this->responseDataFormat = $responseDataFormat;
		$this->environment = $environment;
		$this->securityToken = $token;
	}
	
	/**
	 * Sends a Bulk Data Exchange Request.
	 * @param string $operationName See http://developer.ebaymultiaccount.com/DevZone/bulk-data-exchange/CallRef/index.html for a list of available operations.
	 * @param string $body XML Request
	 * @return string XML Response
	 */
	public function sendBulkDataExchangeRequest($operationName, $body)
	{
		$endpoint = getBulkDataExchangeServiceEndpoint($this->environment);
		$request = new BulkDataExchangeRequest($this->dataFormat,
			$this->responseDataFormat, $this->securityToken, $operationName, $endpoint);
		
		$response = $request->sendHTTPRequest($body);
		
		return $response;
	}
	
	/**
	 * Sends a File Transfer Service Upload Request.
	 * @param string $body XML Request
	 * @return string XML Response
	 */
	public function sendFileTransferServiceUploadRequest($body)
	{
		$endpoint = getFileTransferServiceEndpoint($this->environment);
		
		$request = new FileTransferServiceUploadRequest($this->dataFormat,
			$this->responseDataFormat, $this->securityToken, 'uploadFile', $endpoint);
			
		$response = $request->sendHTTPRequest($body);
		
		return $response;
	}
	
	/**
	 * Sends a File Transfer Service Download Request.
	 * @param string $body XML Request
	 * @return string MIME multipart message.
	 */
	public function sendFileTransferServiceDownloadRequest($body)
	{
		$endpoint = getFileTransferServiceEndpoint($this->environment);
		
		$request = new FileTransferServiceDownloadRequest($this->dataFormat,
			$this->responseDataFormat, $this->securityToken, 'downloadFile', $endpoint);
			
		$response = $request->sendHTTPRequest($body);
		
		return $response;
	}
	
}
