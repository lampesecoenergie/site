<?php

namespace Fooman\PdfDesign\Model;

/**
 * Design source for Alternative Pdf Design Two
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AlternativeDesignTwo extends AlternativeDesign
{

    public function getLayoutHandle($pdfType)
    {
        return sprintf('fooman_pdfcustomiser_alt_2_%s', $pdfType);
    }
}
