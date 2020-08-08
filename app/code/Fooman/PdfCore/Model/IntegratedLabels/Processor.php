<?php
namespace Fooman\PdfCore\Model\IntegratedLabels;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Processor
{
    /**
     * @var \Fooman\PdfCore\Model\Tcpdf\Pdf
     */
    private $pdf;

    /**
     * @var \Fooman\PdfCore\Block\Pdf\DocumentRendererInterface
     */
    private $document;

    private $content;

    public function __construct(
        \Fooman\PdfCore\Block\Pdf\DocumentRendererInterface $document,
        \Fooman\PdfCore\Model\Tcpdf\Pdf $pdf
    ) {
        $this->pdf = $pdf;
        $this->document = $document;
    }

    public function process()
    {
        $this->content = $this->document->getIntegratedLabelsContent();
        $this->writeContent();
    }

    protected function writeContent()
    {
        if ($this->content->getLeft() && $this->content->getRight()) {
            $this->writeDouble();
        } elseif ($this->content->getLeft()) {
            $this->writeSingle();
        }
    }

    protected function writeDouble()
    {
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetXY(-180, -60);
        $this->pdf->writeHTMLCell(75, 0, null, null, $this->content->getLeft(), null, 0);
        $this->pdf->SetXY(-95, -60);
        $this->pdf->writeHTMLCell(75, $this->pdf->getLastH(), null, null, $this->content->getRight(), null, 1);
    }

    protected function writeSingle()
    {
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetXY(-180, -60);
        $this->pdf->writeHTMLCell(75, 0, null, null, $this->content->getLeft(), null, 0);
    }
}
