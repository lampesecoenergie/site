<?php
namespace Fooman\PdfCustomiser\Plugin\Adminhtml;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AbstractPdfPlugin
{

    /**
     * @var \Fooman\PdfCore\Model\PdfRenderer
     */
    protected $pdfRenderer;

    /**
     * @var \Fooman\PdfCore\Model\PdfFileHandling
     */
    protected $pdfFileHandling;

    /**
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling             $pdfFileHandling
     */
    public function __construct(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling
    ) {
        $this->pdfRenderer = $pdfRenderer;
        $this->pdfFileHandling = $pdfFileHandling;
    }

    public function sendPdfFile()
    {
        return $this->pdfFileHandling->sendPdfFile($this->pdfRenderer);
    }
}
