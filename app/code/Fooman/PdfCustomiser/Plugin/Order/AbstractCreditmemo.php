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
class AbstractCreditmemo extends AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

    /**
     * @param \Magento\Framework\Controller\Result\ForwardFactory                          $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                                            $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling                                        $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory                                $creditmemoDocumentFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface                             $creditmemoRepository
     * @param \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                  $orderRepository
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($resultForwardFactory, $pdfRenderer, $pdfFileHandling);

        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
