<?php
namespace Fooman\PdfCustomiser\Model\PdfRenderer;

use Fooman\EmailAttachments\Model\Api\PdfRendererInterface;
use Fooman\PdfCustomiser\Model\EnableNotice;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TermsAndConditionsAdapter implements PdfRendererInterface
{
    private $pdfRendererFactory;

    private $pdfRenderer;

    private $agreementDocumentFactory;

    private $enableNotice;

    public function __construct(
        \Fooman\PdfCore\Model\PdfRendererFactory $pdfRendererFactory,
        \Fooman\PdfCustomiser\Block\AgreementFactory $agreementFactory,
        EnableNotice $enableNotice
    ) {
        $this->pdfRendererFactory = $pdfRendererFactory;
        $this->agreementDocumentFactory = $agreementFactory;
        $this->enableNotice = $enableNotice;
    }

    public function getPdfAsString(array $agreements)
    {
        $this->pdfRenderer = $this->pdfRendererFactory->create();
        foreach ($agreements as $agreement) {
            $document = $this->agreementDocumentFactory->create(
                ['data' => ['agreement' => $agreement]]
            );
            $this->pdfRenderer->addDocument($document);
        }

        return $this->pdfRenderer->getPdfAsString();
    }

    public function getFileName()
    {
        return $this->pdfRenderer->getFilename(true);
    }

    public function canRender()
    {
        return $this->enableNotice->canRender();
    }
}
