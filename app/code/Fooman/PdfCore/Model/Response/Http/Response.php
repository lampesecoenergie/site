<?php
namespace Fooman\PdfCore\Model\Response\Http;

use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Response\Http;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Response extends Http implements NotCacheableInterface
{
    const XML_PATH_OPEN_IN_BROWSER = 'sales_pdf/all/openinbrowser';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Request\Http                    $request
     * @param \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Http\Context                    $context
     * @param \Magento\Framework\Stdlib\DateTime                     $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface     $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime);
    }

    public function sendHeaders()
    {
        if ($this->shouldPrintInBrowser()) {
            $this->inlineDispositionHeader();
        }
        return parent::sendHeaders();
    }

    public function shouldPrintInBrowser()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OPEN_IN_BROWSER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    protected function inlineDispositionHeader()
    {
        $disposition = $this->getHeader('Content-Disposition')->getFieldValue();
        if (substr($disposition, 0, strlen('attachment;')) == 'attachment;') {
            $disposition = substr($disposition, strlen('attachment;'));
            $this->setHeader('Content-Disposition', 'inline; ' . $disposition, true);
        }
    }
}
