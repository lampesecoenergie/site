<?php

class MultiPartMessage
{	
	public static $CRLF = "\r\n";
	public static $MIME_BOUNDARY = "MIME_boundary";
	
	//!NOTE! 
	//If using this in a Production-style setting, UUIDs should 
	//be generated at runtime.
	public static $URN_UUID_REQUEST = "<0.urn:uuid:4A515E9048570>";
	public static $URN_UUID_ATTACHMENT = "urn:uuid:4A51606E0C0D1";
	
	/**
	 * Builds a MIME multipart message comprised of the
	 * XML Request as well as the File attachment.
	 * @param string $request XML Request
	 * @param string $file bytes comprising the file 
	 * @return string Complete MIME multipart message
	 */
	public static function build($request, $file)
	{
		$requestPart   = '';
	    $requestPart  .= "--" . self::$MIME_BOUNDARY . self::$CRLF;
	    $requestPart  .= 'Content-Type: application/xop+xml; charset=UTF-8; type="text/xml; charset=UTF-8"' . self::$CRLF;
	    $requestPart  .= 'Content-Transfer-Encoding: binary' . self::$CRLF;
	    $requestPart  .= 'Content-ID: ' . self::$URN_UUID_REQUEST . self::$CRLF . self::$CRLF;
	    $requestPart  .= $request . self::$CRLF;
		
	    $binaryPart = '';
	    $binaryPart .= "--" . self::$MIME_BOUNDARY . self::$CRLF;
	    $binaryPart .= 'Content-Type: application/octet-stream' . self::$CRLF;
	    $binaryPart .= 'Content-Transfer-Encoding: binary' . self::$CRLF;
	    $binaryPart .= 'Content-ID: <' . self::$URN_UUID_ATTACHMENT . '>' . self::$CRLF . self::$CRLF;
	    $binaryPart .= $file . self::$CRLF;
	    $binaryPart .= "--" . self::$MIME_BOUNDARY . "--";
	    
	   return $requestPart . $binaryPart;
	}	
		
}

?>