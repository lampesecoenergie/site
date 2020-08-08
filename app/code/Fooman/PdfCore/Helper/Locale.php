<?php

namespace Fooman\PdfCore\Helper;

use Fooman\PdfCore\Block\Pdf\DocumentRendererInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Locale
{
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isRightToLeft(DocumentRendererInterface $document)
    {
        $scopeConfig = $document->getScopeConfig();
        $locale = $scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $document->getStoreId()
        );

        return $this->isLocaleRightToLeft($locale);
    }

    public function isRightToLeftByStoreId($storeId)
    {
        $locale = $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->isLocaleRightToLeft($locale);
    }

    public function isLocaleRightToLeft($locale)
    {
        return $locale === 'he_IL' || strpos($locale, 'ar_') === 0 || $locale === 'fa_IR';
    }
}
