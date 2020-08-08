<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Barcode extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 28;
    const DEFAULT_TITLE = '';

    const COLUMN_TYPE = 'fooman_barcode';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $data);
    }

    public function getGetter()
    {
        return [$this, 'getBarcode'];
    }

    public function getBarcode($row)
    {
        $sku = $this->getSku($row);
        $barcodeParams = [
            $this->escapeHtml($sku),
            $this->getBarcodeType(),
            //the parameters below refer to x, y, width, and height of the barcode respectively
            '', '', '35', '13'
        ];
        return sprintf(
            '<table><tr><td height="13mm"><tcpdf method="write1DBarcode" %s /></td></tr></table>',
            $this->paramKeyHelper->getEncodedParams($barcodeParams)
        );
    }

    public function getSku($row)
    {
        $attribute = 'sku';
        // we use product repository here to in the future allow any attribute as source for the barcode
        // for example EAN
        try {
            $product = $this->productRepository->getById($row->getProductId(), false, $row->getStoreId());
            $sku = $product->getData($attribute);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $sku = $row->getDataUsingMethod($attribute);
        }
        return $sku;
    }

    public function getBarcodeType()
    {
        return $this->_scopeConfig->getValue(
            \Fooman\PdfCore\Block\Pdf\PdfAbstract::XML_PATH_BARCODE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
