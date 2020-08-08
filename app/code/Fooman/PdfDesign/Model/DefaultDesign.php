<?php

namespace Fooman\PdfDesign\Model;

/**
 * Design source for Default Pdf Design
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DefaultDesign implements Api\DesignInterface
{
    private $storeId;
    private $templateFiles;
    private $footerHandle;

    public function __construct(
        array $templateFiles = [],
        $footerHandle = \Fooman\PdfCore\Block\Pdf\DocumentRenderer::DEFAULT_FOOTER_LAYOUT_HANDLE
    ) {
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
        return sprintf('fooman_pdfcustomiser_%s', $pdfType);
    }

    /**
     * @return array
     */
    public function getItemStyling()
    {
        return [
            'header' => [
                'default' => 'border-bottom:1px solid black;',
                'first' => 'border-bottom:1px solid black;',
                'last' => 'border-bottom:1px solid black;'
            ],
            'row' => [
                'default' => 'border-bottom:0px none transparent;',
                'last' => 'border-bottom:0px solid black;',
                'first' => 'border-bottom:0px none transparent;'
            ],
            'table' => ['default' => 'padding: 2px 0px;']
        ];
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
