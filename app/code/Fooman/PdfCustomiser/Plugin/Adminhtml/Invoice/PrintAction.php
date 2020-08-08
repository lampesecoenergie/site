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
class PrintAction extends AbstractInvoice
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction $subject
     * @param \Closure                                                                $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction $subject,
        \Closure $proceed
    ) {
        $invoiceId = $subject->getRequest()->getParam('invoice_id');

        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if ($invoice) {
                $document = $this->invoiceDocumentFactory->create(
                    ['data' => ['invoice' => $invoice]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        }
        return $proceed();
    }
}
