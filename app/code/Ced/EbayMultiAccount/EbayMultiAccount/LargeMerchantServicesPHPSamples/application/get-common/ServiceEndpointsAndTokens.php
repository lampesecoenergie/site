<?php
    
    define("ENV_PRODUCTION", 'production');
    define("ENV_SANDBOX", 'sandbox');
    
    function getBulkDataExchangeServiceEndpoint($environment)
    {
	    if ( $environment == ENV_PRODUCTION ) {
	        $endpoint = 'https://webservices.ebay.com/BulkDataExchangeService';
	    }
	    elseif ( $environment == ENV_SANDBOX ) {  
	    	$endpoint = 'https://webservices.sandbox.ebay.com/BulkDataExchangeService';
	    }
	    else {
	    	die("Invalid Environment: $environment");  
	    }	    
	    return $endpoint;
    }
    
    function getFileTransferServiceEndpoint($environment)
    {
    	if ( $environment == ENV_PRODUCTION ) {
	        $endpoint = 'https://storage.ebay.com/FileTransferService';
	    }
	    elseif ( $environment == ENV_SANDBOX ) {  
	    	$endpoint = 'https://storage.sandbox.ebay.com/FileTransferService';
	    }
	    else {
	    	die("Invalid Environment: $environment");    
	    }
	    
	    return $endpoint;
    }
    
    function getSecurityToken($environment)
    {
	    if ( $environment === ENV_PRODUCTION ) {
	        $securityToken = 'PASTE_YOUR_PRODUCTION_TOKEN_HERE';
	    }
	    elseif ( $environment === ENV_SANDBOX ) {  
	        $securityToken = 'PASTE_YOUR_SANDBOX_TOKEN_HERE';                 
	    }
	    else {
	    	die("Invalid Environment: $environment");   
	    }
	    
	    return $securityToken;
    }
