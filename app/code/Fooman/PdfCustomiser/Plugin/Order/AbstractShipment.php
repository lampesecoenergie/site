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
class AbstractShipment extends AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

    /**
     * @param \Magento\Framework\Controller\Result\ForwardFactory                          $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                                            $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling                                        $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                                  $shipmentDocumentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface                               $shipmentRepository
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                  $orderRepository
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($resultForwardFactory, $pdfRenderer, $pdfFileHandling);

        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
