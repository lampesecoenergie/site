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
class GuestPrintInvoice extends AbstractGuestInvoice
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\PrintInvoice $subject
     * @param \Closure                                                  $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\AbstractController\PrintInvoice $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {
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
                } elseif ($orderId == $order->getId()) {
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
        }
        return $this->resultForwardFactory->create()->forward('form');
    }
}
