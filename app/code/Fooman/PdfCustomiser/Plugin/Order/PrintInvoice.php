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
class PrintInvoice extends AbstractInvoice
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintInvoice $subject
     * @param \Closure                                     $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintInvoice $subject,
        \Closure $proceed
    ) {
        $invoiceId = (int)$subject->getRequest()->getParam('invoice_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if ($invoice && $this->orderViewAuthorization->canView($invoice->getOrder())) {
                $document = $this->invoiceDocumentFactory->create(
                    ['data' => ['invoice' => $invoice]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order && $this->orderViewAuthorization->canView($order)) {
                $invoices = $order->getInvoiceCollection();
                if ($invoices) {
                    foreach ($invoices as $invoice) {
                        $document = $this->invoiceDocumentFactory->create(
                            ['data' => ['invoice' => $invoice]]
                        );

                        $this->pdfRenderer->addDocument($document);
                    }
                    return $this->sendPdfFile();
                }
            }
        }
        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
