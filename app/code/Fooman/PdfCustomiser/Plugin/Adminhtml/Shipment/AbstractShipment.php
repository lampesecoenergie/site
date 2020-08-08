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
class AbstractShipment extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling             $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory       $shipmentDocumentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface    $shipmentRepositoryInterface
     */
    public function __construct(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface
    ) {
        parent::__construct($pdfRenderer, $pdfFileHandling);

        $this->shipmentRepository = $shipmentRepositoryInterface;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }
}
