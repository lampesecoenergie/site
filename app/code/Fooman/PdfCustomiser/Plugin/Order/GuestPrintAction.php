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
class GuestPrintAction extends AbstractGuestOrder
{
    /**
     * @param \Magento\Sales\Controller\Guest\PrintAction $subject
     * @param \Closure                                    $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Guest\PrintAction $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {
                $document = $this->orderDocumentFactory->create(
                    ['data' => ['order' => $order]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        }
        return $this->resultForwardFactory->create()->forward('form');
    }
}
