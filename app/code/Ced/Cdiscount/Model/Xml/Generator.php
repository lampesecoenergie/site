<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/3/18
 * Time: 5:10 PM
 */

namespace Ced\Cdiscount\Model\Xml;


class Generator extends \Magento\Framework\Xml\Generator
{

    /**
     *
     */
    public function __construct()
    {
        $this->_dom = new \DOMDocument('1.0', 'UTF-8');
        $this->_dom->formatOutput = true;
        $this->_currentDom = $this->_dom;
        return $this;
    }
    /**
     * @param array $content
     * @return $this
     * @throws \DOMException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function arrayToXml($content)
    {
        $parentNode = $this->_getCurrentDom();
        if (!$content || !count($content)) {
            return $this;
        }
        foreach ($content as $_key => $_item) {
            $node = $this->getDom()->createElement($_key);
            $parentNode->appendChild($node);
            if (is_array($_item) && isset($_item['_attribute'])) {
                if (is_array($_item['_value'])) {
                    if (isset($_item['_value'][0])) {
                        foreach ($_item['_value'] as $_v) {
                            $this->_setCurrentDom($node)->arrayToXml($_v);
                        }
                    } else {
                        $this->_setCurrentDom($node)->arrayToXml($_item['_value']);
                    }
                } else {
                    $child = $this->getDom()->createCDATASection($_item['_value']);
                    $node->appendChild($child);
                }
                foreach ($_item['_attribute'] as $_attributeKey => $_attributeValue) {
                    $node->setAttribute($_attributeKey, $_attributeValue);
                }
            } elseif (is_string($_item)) {
                $text = $this->getDom()->createCDATASection($_item);
                $node->appendChild($text);
            } elseif (is_array($_item) && !isset($_item[0])) {
                $this->_setCurrentDom($node)->arrayToXml($_item);
            } elseif (is_array($_item) && isset($_item[0])) {
                foreach ($_item as $v) {
                    $this->_setCurrentDom($node)->arrayToXml([$this->_getIndexedArrayItemName() => $v]);
                }
            }
        }
        return $this;
    }

}