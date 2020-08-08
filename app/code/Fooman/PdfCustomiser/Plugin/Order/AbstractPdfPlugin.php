<?php
namespace Fooman\PdfCustomiser\Plugin\Order;

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
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Fooman\PdfCore\Model\PdfFileHandling
     */
    protected $pdfFileHandling;

    /**
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                   $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling               $pdfFileHandling
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->pdfRenderer = $pdfRenderer;
        $this->pdfFileHandling = $pdfFileHandling;
    }

    public function sendPdfFile()
    {
        return $this->pdfFileHandling->sendPdfFile($this->pdfRenderer);
    }
}
