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
class ShipmentAdapter implements PdfRendererInterface
{
    private $pdfRendererFactory;

    private $pdfRenderer;

    private $shipmentDocumentFactory;

    private $enableNotice;

    public function __construct(
        \Fooman\PdfCore\Model\PdfRendererFactory $pdfRendererFactory,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        EnableNotice $enableNotice
    ) {
        $this->pdfRendererFactory = $pdfRendererFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->enableNotice = $enableNotice;
    }

    public function getPdfAsString(array $salesObjects)
    {
        $this->pdfRenderer = $this->pdfRendererFactory->create();
        foreach ($salesObjects as $shipment) {
            $document = $this->shipmentDocumentFactory->create(
                ['data' => ['shipment' => $shipment]]
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
