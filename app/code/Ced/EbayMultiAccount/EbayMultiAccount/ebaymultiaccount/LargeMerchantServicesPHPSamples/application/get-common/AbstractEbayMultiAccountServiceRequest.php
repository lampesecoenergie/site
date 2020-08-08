<?php
/**
 * Base implementation for an eBay SOA Service Request.
 */
abstract class AbstractEbayMultiAccountServiceRequest
{
	abstract protected function getHeaders();
	abstract protected function setAdditionalOptions($connection);
	
	private $dataFormat;
	private $responseDataFormat;
	private $securityToken;
	private $operationName;
	private $endpoint;
	
	public function __construct($dataFormat, $responseDataFormat, $securityToken,
		$operationName, $endpoint)
	{
		$this->dataFormat = $dataFormat;
		$this->responseDataFormat = $responseDataFormat;
		$this->securityToken = $securityToken;
		$this->operationName = $operationName;
		$this->endpoint = $endpoint;
	}
	
	/**
	 * Sends an HTTP Request to the desired endpoint with
	 * the appropriate headers using cURL.
	 * 
	 * @param string $request XML Request
	 * @return string The Response in XML.
	 */
	public function sendHTTPRequest($request)
	{
		//Initialise a CURL session
		$connection = curl_init();
		
		//Set the endpoint to the environment desired
		curl_setopt($connection, CURLOPT_URL, $this->endpoint);
		
		//Stop CURL from verifying the peer's certificate
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		
		//Set the HTTP Headers
		$headers = $this->getHeaders();
		curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
		
		//Set method as POST
		curl_setopt($connection, CURLOPT_POST, 1);
		
		//Set the XML body of the request
		curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
		
		//Set it to return the transfer as a string from curl_exec
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		
		$this->setAdditionalOptions($connection);
		
		//Send the Request
		$response = curl_exec($connection);
		
		//Check for any errors
		if( curl_errno($connection) )
		{
		    echo 'Curl Error: ' . curl_error($connection);
		}
		
		//Close the connection
		curl_close($connection);
		
		//Return the response
		return $response;
	}
	
	/**
	 * Returns the basic HTTP Headers required for an eBay SOA
	 * Service Request.
	 * @return array
	 */
	protected function getHeadersBaseline()
	{
		$headers = array (
			'X-EBAY-SOA-REQUEST-DATA-FORMAT: ' . $this->dataFormat,
			'X-EBAY-SOA-RESPONSE-DATA-FORMAT: ' . $this->responseDataFormat,
			'X-EBAY-SOA-SECURITY-TOKEN: ' . $this->securityToken,
			'X-EBAY-SOA-OPERATION-NAME: ' . $this->operationName
		);
		
		return $headers;
	}
}
