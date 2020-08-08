<?php
namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Order;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AbstractOrder extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $orderDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling             $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\OrderFactory          $orderDocumentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface       $orderRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($pdfRenderer, $pdfFileHandling);

        $this->orderRepository = $orderRepository;
        $this->orderDocumentFactory = $orderDocumentFactory;
    }
}
