<?php
namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Invoice;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AbstractInvoice extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    protected $invoiceDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling             $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory        $invoiceDocumentFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface     $invoiceRepositoryInterface
     */
    public function __construct(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepositoryInterface
    ) {
        parent::__construct($pdfRenderer, $pdfFileHandling);

        $this->invoiceRepository = $invoiceRepositoryInterface;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
    }
}
