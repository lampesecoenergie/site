<?php

namespace Fooman\PdfDesign\Model;

/**
 * Design source for Alternative Pdf Design
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AlternativeDesign implements Api\DesignInterface
{
    const XML_PATH_PRIMARY_COLOUR = 'sales_pdf/altdesign/primarycolour';
    const XML_PATH_SECONDARY_COLOUR = 'sales_pdf/altdesign/secondarycolour';

    private $storeId;
    private $scopeConfig;
    private $templateFiles;
    private $footerHandle;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $templateFiles = [],
        $footerHandle = \Fooman\PdfCore\Block\Pdf\DocumentRenderer::DEFAULT_FOOTER_LAYOUT_HANDLE
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->templateFiles = $templateFiles;
        $this->footerHandle = $footerHandle;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    public function getLayoutHandle($pdfType)
    {
        return sprintf('fooman_pdfcustomiser_alt_%s', $pdfType);
    }

    /**
     * @return array
     */
    public function getItemStyling()
    {
        $primCol = $this->getPrimaryColour();
        $secCol = $this->getSecondaryColour();
        return [
            'header' => [
                'default' => "font-weight:bold; 
                border-top:2px solid $secCol; 
                border-bottom:2px solid $secCol; 
                background-color: $primCol;",
                'first'   => "font-weight:bold; 
                border-top:2px solid $secCol; 
                border-bottom:2px solid $secCol; 
                background-color: $primCol; 
                border-left:2px solid $secCol;",
                'last'    => "font-weight:bold; 
                border-top:2px solid $secCol; 
                border-bottom:2px solid $secCol; 
                background-color: $primCol; 
                border-right:2px solid $secCol;"
            ],
            'row'    => [
                'default' => 'border-bottom:0px none transparent;',
                'last'    => "border-bottom:2px solid $secCol;",
                'first'   => 'border-bottom:0px none transparent;'
            ],
            'cell'    => [
                'default' => '',
                'last'    => "border-right:2px solid $secCol;",
                'first'   => "border-left:2px solid $secCol;"
            ],
            'table'  => ['default' => 'padding: 8px 0px;']
        ];
    }

    public function getPrimaryColour()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIMARY_COLOUR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getSecondaryColour()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SECONDARY_COLOUR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getHeaderStyle($isFirst, $isLast)
    {
        return $this->getStyle('header', $isFirst, $isLast);
    }
    public function getCellStyle($isFirstRow, $isLastRow, $isFirstCell, $isLastCell)
    {
        return $this->getStyle('row', $isFirstRow, $isLastRow). ' '. $this->getStyle('cell', $isFirstCell, $isLastCell);
    }

    public function getStyle($type, $isFirst, $isLast)
    {
        if ($isFirst && $isLast) {
            return $this->getItemStyling()[$type]['first'] . ' '. $this->getItemStyling()[$type]['last'];
        }

        if ($isFirst) {
            return $this->getItemStyling()[$type]['first'];
        }

        if ($isLast) {
            return $this->getItemStyling()[$type]['last'];
        }
        return $this->getItemStyling()[$type]['default'];
    }

    public function getTemplateFiles()
    {
        return $this->templateFiles;
    }

    public function getFooterLayoutHandle()
    {
        return $this->footerHandle;
    }
}
