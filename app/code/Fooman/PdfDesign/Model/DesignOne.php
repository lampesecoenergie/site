<?php

namespace Fooman\PdfDesign\Model;

/**
 * Design source for pdf design "Design One"
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DesignOne implements Api\DesignInterface
{
    const XML_PATH_PRIMARY_COLOUR = 'sales_pdf/designone/primarycolour';
    const XML_PATH_SECONDARY_COLOUR = 'sales_pdf/designone/secondarycolour';

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
        return sprintf('fooman_pdfcustomiser_design_1_%s', $pdfType);
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
                'default' => "border-bottom:4px solid $primCol; 
                 border-top:4px solid $primCol;
                 background-color: $primCol; color: $secCol; 
                 font-weight:bold; border-left:4px solid $primCol;",

                'first' => "border-bottom:4px solid $primCol;
                border-top:4px solid $primCol;
                background-color: $primCol; color: $secCol; 
                font-weight:bold; border-left:4px solid $primCol;",

                'last' => "border-bottom:4px solid $primCol ;
                border-top:4px solid $primCol;
                background-color: $primCol; color: $secCol;
                font-weight:bold; border-left:4px solid $primCol;
                border-right:4px solid $primCol;",
            ],
            'row'    => [
                'default' => 'border-bottom:4px none transparent;',
                'last'    => "border-bottom:4px solid $primCol;",
                'first'   => 'border-bottom:4px none transparent;'
            ],
            'row-inv'    => [
                'default' => "border-bottom:4px none transparent; color: $secCol; background-color: $primCol;",
                'last'    => "border-bottom:4px solid $primCol; color: $secCol; background-color: $primCol;",
                'first'   => "border-bottom:4px none transparent; color: $secCol; background-color: $primCol;",
            ],
            'cell'    => [
                'default' => "border-left:4px solid $primCol;",
                'last'    => "border-left:4px solid $primCol; border-right:4px solid $primCol;",
                'first'   => "border-left:4px solid $primCol;"
            ],
            'cell-inv'    => [
                'default' => '',
                'last'    => "border-right:4px solid $primCol;",
                'first'   => "border-left:4px solid $primCol;"
            ],
            'table'  => ['default' => 'padding: 10px 0px;']
        ];
    }

    public function getTemplateFiles()
    {
        return $this->templateFiles;
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

    /**
     * @param string $hex
     *
     * @return array
     */
    public function hexToRGBArray($hex)
    {
        return sscanf($hex, "#%02x%02x%02x");
    }

    public function getFooterLayoutHandle()
    {
        return $this->footerHandle;
    }

    public function getHeaderStyle($isFirst, $isLast)
    {
        return $this->getStyle('header', $isFirst, $isLast);
    }

    public function getRowStyle($isFirst, $isLast)
    {
        return $this->getStyle('row', $isFirst, $isLast);
    }

    public function getTableStyle()
    {
        return $this->getStyle('table', false, false);
    }

    public function getCellStyle($isFirstRow, $isLastRow, $isFirstCell, $isLastCell)
    {
        return $this->getStyle('row', $isFirstRow, $isLastRow). ' '. $this->getStyle('cell', $isFirstCell, $isLastCell);
    }

    public function getInvertedCellStyle($isFirstRow, $isLastRow, $isFirstCell, $isLastCell)
    {
        return $this->getStyle('row-inv', $isFirstRow, $isLastRow). ' '
            . $this->getStyle('cell-inv', $isFirstCell, $isLastCell);
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
}
