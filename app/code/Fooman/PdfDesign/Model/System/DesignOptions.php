<?php
namespace Fooman\PdfDesign\Model\System;

/**
 * Display all configured designs as dropdown choices
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DesignOptions implements \Magento\Framework\Data\OptionSourceInterface
{

    private $pdfDesignConfig;

    public function __construct(
        \Fooman\PdfDesign\Model\Config\PdfDesignData $designData
    ) {
        $this->pdfDesignConfig = $designData;
    }

    public function toOptionArray()
    {
        return $this->pdfDesignConfig->getPdfDesignOptions();
    }
}
