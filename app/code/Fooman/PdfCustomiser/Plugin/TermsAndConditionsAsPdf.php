<?php
namespace Fooman\PdfCustomiser\Plugin;

use Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TermsAndConditionsAsPdf
{
    private $contentAttacher;

    private $pdfRenderer;

    private $termsCollection;

    /**
     * @param \Fooman\EmailAttachments\Model\ContentAttacher                              $contentAttacher
     * @param \Fooman\PdfCustomiser\Model\PdfRenderer\TermsAndConditionsAdapter           $pdfRenderer
     * @param \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $termsCollection
     */
    public function __construct(
        \Fooman\EmailAttachments\Model\ContentAttacher $contentAttacher,
        \Fooman\PdfCustomiser\Model\PdfRenderer\TermsAndConditionsAdapter $pdfRenderer,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $termsCollection
    ) {
        $this->contentAttacher = $contentAttacher;
        $this->pdfRenderer = $pdfRenderer;
        $this->termsCollection = $termsCollection;
    }

    public function aroundAttachForStore(
        \Fooman\EmailAttachments\Model\TermsAndConditionsAttacher $subject,
        \Closure $proceed,
        $storeId,
        ContainerInterface $attachmentContainer
    ) {
        /**
         * @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection $agreements
         */
        $agreements = $this->termsCollection->create();
        $agreements->addStoreFilter($storeId)->addFieldToFilter('is_active', 1);

        foreach ($agreements as $agreement) {
            $agreement->setStoreId($storeId);
            $this->attachAgreement($agreement, $attachmentContainer);
        }
    }

    private function attachAgreement(
        AgreementInterface $agreement,
        ContainerInterface $attachmentContainer
    ) {
        $this->contentAttacher->addPdf(
            $this->pdfRenderer->getPdfAsString([$agreement]),
            $this->pdfRenderer->getFileName(),
            $attachmentContainer
        );
    }
}
