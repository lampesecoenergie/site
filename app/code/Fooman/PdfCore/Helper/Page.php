<?php

namespace Fooman\PdfCore\Helper;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PDF_PAGE_ORIENTATION = 'sales_pdf/all/allpageorientation';
    const XML_PATH_PDF_PAGE_SIZE = 'sales_pdf/all/allpagesize';

    /**
     * Page Size
     * Current supported sizes A4 and US Letter
     *
     * @return mixed
     */
    public function getSize()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PDF_PAGE_SIZE
        );
    }

    /**
     * Page Orientation (Portrait or Landscape)
     *
     * @return mixed
     */
    public function getOrientation()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PDF_PAGE_ORIENTATION
        );
    }

    /**
     * Page Margin on left and right (in mm)
     *
     * @return mixed
     */
    public function getSideMargins()
    {
        return $this->scopeConfig->getValue(
            \Fooman\PdfCore\Model\PdfRenderer::XML_PATH_SIDE_MARGINS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Take page size and orientation and work out
     * page width in mm
     *
     * @return float
     */
    public function getPageWidth()
    {
        $size = \TCPDF_STATIC::getPageSizeFromFormat($this->getSize());

        if ($this->getOrientation() == \Fooman\PdfCore\Model\Config\Source\Pageorientation::PORTRAIT) {
            $width = $size[0];
        } else {
            $width = $size[1];
        }

        //convert to mm
        return round($width * 25.4 / 72, 2);
    }
}
