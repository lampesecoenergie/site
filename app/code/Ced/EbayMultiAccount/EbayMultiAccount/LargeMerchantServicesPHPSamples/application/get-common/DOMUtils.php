<?php

class DOMUtils {

	/**
	 * Creates a DOM for XML; Defaults to using pretty print.
	 * @param string $xml XML Blob
	 * @return DomDocument DOM representation of the XML
	 */
	public static function createDOM($xml)
	{
		$dom = new DomDocument();
		$dom->preserveWhitespace = false;
		$dom->loadXML($xml);
		$dom->formatOutput = true;
		
		return $dom;
	}
	
}
