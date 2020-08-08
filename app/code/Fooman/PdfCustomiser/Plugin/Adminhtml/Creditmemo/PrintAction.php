<?php
namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Creditmemo;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PrintAction extends AbstractCreditmemo
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction $subject
     * @param \Closure                                                                      $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction $subject,
        \Closure $proceed
    ) {
        $creditmemoId = $subject->getRequest()->getParam('creditmemo_id');

        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if ($creditmemo) {
                $document = $this->creditmemoDocumentFactory->create(
                    ['data' => ['creditmemo' => $creditmemo]]
                );

                $this->pdfRenderer->addDocument($document);

                return $this->sendPdfFile();
            }
        }
        return $proceed();
    }
}
