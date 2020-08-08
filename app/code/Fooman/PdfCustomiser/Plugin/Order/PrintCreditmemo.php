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
class PrintCreditmemo extends AbstractCreditmemo
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintCreditmemo $subject
     * @param \Closure                                                     $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintCreditmemo $subject,
        \Closure $proceed
    ) {
        $creditmemoId = (int)$subject->getRequest()->getParam('creditmemo_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if ($creditmemo && $this->orderViewAuthorization->canView($creditmemo->getOrder())) {
                $document = $this->creditmemoDocumentFactory->create(
                    ['data' => ['creditmemo' => $creditmemo]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order && $this->orderViewAuthorization->canView($order)) {
                $creditmemos = $order->getCreditmemosCollection();
                if ($creditmemos) {
                    foreach ($creditmemos as $creditmemo) {
                        $document = $this->creditmemoDocumentFactory->create(
                            ['data' => ['creditmemo' => $creditmemo]]
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
