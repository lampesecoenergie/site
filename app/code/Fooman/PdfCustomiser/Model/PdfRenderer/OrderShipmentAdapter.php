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
class OrderShipmentAdapter implements PdfRendererInterface
{
    private $pdfRendererFactory;

    private $pdfRenderer;

    private $orderShipmentDocumentFactory;

    private $enableNotice;

    public function __construct(
        \Fooman\PdfCore\Model\PdfRendererFactory $pdfRendererFactory,
        \Fooman\PdfCustomiser\Block\OrderShipmentFactory $shipmentDocumentFactory,
        EnableNotice $enableNotice
    ) {
        $this->pdfRendererFactory = $pdfRendererFactory;
        $this->orderShipmentDocumentFactory = $shipmentDocumentFactory;
        $this->enableNotice = $enableNotice;
    }

    public function getPdfAsString(array $salesObjects)
    {
        $this->pdfRenderer = $this->pdfRendererFactory->create();
        foreach ($salesObjects as $order) {
            $document = $this->orderShipmentDocumentFactory->create(
                ['data' => ['order' => $order]]
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
