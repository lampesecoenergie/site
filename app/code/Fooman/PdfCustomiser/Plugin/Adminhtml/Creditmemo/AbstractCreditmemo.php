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
class AbstractCreditmemo extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCore\Model\PdfFileHandling             $pdfFileHandling
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory     $creditmemoDocumentFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface  $creditmemoRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCore\Model\PdfFileHandling $pdfFileHandling,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        parent::__construct($pdfRenderer, $pdfFileHandling);

        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
    }
}
