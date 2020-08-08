<?php
namespace Fooman\PdfDesign\Block\DesignOne;

use Fooman\PdfCore\Helper\ParamKey;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Block class for adaptive icons
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Icons extends \Fooman\PdfCore\Block\Pdf\Block
{

    /**
     * @var ParamKey
     */
    private $paramKeyHelper;

    /**
     * @param Context  $context
     * @param ParamKey $paramKeyHelper
     * @param array    $data
     */
    public function __construct(
        Context $context,
        ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getEncodedParams(array $params)
    {
        return $this->paramKeyHelper->getEncodedParams($params);
    }

    public function getCity()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/city',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getStreetAddress()
    {
        $streets = [];
        $streetOne = $this->_scopeConfig->getValue(
            'general/store_information/street_line1',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $streetTwo = $this->_scopeConfig->getValue(
            'general/store_information/street_line2',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($streetOne) {
            $streets[] = $streetOne;
        }
        if ($streetTwo) {
            $streets[] = $streetTwo;
        }

        return implode(', ', $streets);
    }

    public function getPostcode()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/postcode',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getPhone()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_custom1/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getWebsite()
    {
        $url = $this->_scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $urlProtFree = rtrim(str_replace(['https://', 'http://'], '', $url), '/');
        return sprintf('<a style="color:black; text-decoration: none;" href="%s">%s</a>', $url, $urlProtFree);
    }
}
