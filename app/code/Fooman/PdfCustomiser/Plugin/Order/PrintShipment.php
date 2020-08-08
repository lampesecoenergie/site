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
class PrintShipment extends AbstractShipment
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintShipment $subject
     * @param \Closure                                      $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintShipment $subject,
        \Closure $proceed
    ) {
        $shipmentId = (int)$subject->getRequest()->getParam('shipment_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment && $this->orderViewAuthorization->canView($shipment->getOrder())) {
                $document = $this->shipmentDocumentFactory->create(
                    ['data' => ['shipment' => $shipment]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order && $this->orderViewAuthorization->canView($order)) {
                $shipments = $order->getShipmentsCollection();
                if ($shipments) {
                    foreach ($shipments as $shipment) {
                        $document = $this->shipmentDocumentFactory->create(
                            ['data' => ['shipment' => $shipment]]
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
