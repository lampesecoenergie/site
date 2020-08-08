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
class PrintAction extends AbstractOrder
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintAction $subject
     * @param \Closure                                                 $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintAction $subject,
        \Closure $proceed
    ) {
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order && $this->orderViewAuthorization->canView($order)) {
                $document = $this->orderDocumentFactory->create(
                    ['data' => ['order' => $order]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        }
        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
