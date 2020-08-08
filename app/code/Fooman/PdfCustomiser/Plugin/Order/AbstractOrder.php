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
class AbstractOrder extends AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\OrderFactory
     */
    protected $orderDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

    /**
     * @param \Magento\Framework\Controller\Result\ForwardFactory                          $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                                            $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling                                        $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\OrderFactory                                     $orderDocumentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                  $orderRepository
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization
    ) {
        parent::__construct($resultForwardFactory, $pdfRenderer, $pdfFileHandling);

        $this->orderRepository = $orderRepository;
        $this->orderDocumentFactory = $orderDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
