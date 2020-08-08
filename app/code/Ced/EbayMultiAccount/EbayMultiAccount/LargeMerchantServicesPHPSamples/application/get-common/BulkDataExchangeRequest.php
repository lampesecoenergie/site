<?php require_once('AbstractEbayMultiAccountServiceRequest.php') ?>
<?php

/**
 * Implementation for Bulk Data Exchange Service Requests.
 */
class BulkDataExchangeRequest extends AbstractEbayMultiAccountServiceRequest
{
	
	public function __construct($dataFormat, $responseDataFormat, $securityToken,
		$operationName, $endpoint)
	{	
		parent::__construct($dataFormat, $responseDataFormat, $securityToken,
			$operationName, $endpoint);
	}
	
	protected function getHeaders()
	{	
		$headers = parent::getHeadersBaseline();
		array_push($headers, 'X-EBAY-SOA-SERVICE-NAME: BulkDataExchangeService');
		
		return $headers;
	}
	
	protected function setAdditionalOptions($connection)
	{
		//No additional options necessary.
	}
	
}
