<?php
namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Shipment;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PrintAction extends AbstractShipment
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\PrintAction $subject
     * @param \Closure                                                                  $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\PrintAction $subject,
        \Closure $proceed
    ) {
        $shipmentId = $subject->getRequest()->getParam('shipment_id');

        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment) {
                $document = $this->shipmentDocumentFactory->create(
                    ['data' => ['shipment' => $shipment]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        }
        return $proceed();
    }
}
