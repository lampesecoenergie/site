<?php

namespace Potato\Crawler\Model\Source\Url;

/**
 * Class Sitemap
 */
class Sitemap
{
    protected $xml = null;
    
    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->xml = $this->_loadXml($path);
        return $this;
    }

    protected function _loadXml($path)
    {
        return @simplexml_load_string(file_get_contents($path));
    }

    /**
     * @return array
     */
    public function getStoreUrls()
    {
        $result = [];
        if (!$this->xml || !$this->xml instanceof \SimpleXMLElement) {
            return $result;
        }
        if ($this->xml->sitemap) {
            foreach ($this->xml->sitemap as $key => $node) {
                $this->_getFromChildFile((string)$node->loc, $result);
            }
        }
        if ($this->xml->url) {
            $this->_getFromChildFile($this->xml, $result);
        }
        ksort($result);
        return $result;
    }

    /**
     * @param $xml
     * @param $result
     * @return bool
     */
    protected function _getFromChildFile($xml, &$result)
    {
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = $this->_loadXml($xml);
        }
        if (!$xml) {
            return false;
        }
        foreach ($xml->url as $key => $node) {
            $priority = (string)$node->priority * 100;
            $result[(int)$priority][] = (string)$node->loc;
        }
        return $result;
    }
}