<?php
namespace Fooman\PdfCore\Block\Pdf\Template;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Footer extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration -- Magento 2 Core use
    protected $_template = 'Fooman_PdfCore::pdf/footer.phtml';

    private $parameters = [];
    private $paramKeyHelper;
    private $pageHelper;

    const MARGIN_IN_BETWEEN = 5; //in percent

    const XML_PATH_FOOTER = 'sales_pdf/all/allfooter';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        \Fooman\PdfCore\Helper\Page $pageHelper,
        array $data = []
    ) {
        $this->paramKeyHelper = $paramKeyHelper;
        $this->pageHelper = $pageHelper;
        parent::__construct($context, $data);
    }

    /**
     * do we have any footer content to output
     *
     * @return bool
     * @access public
     */
    public function hasFooter()
    {
        $footers = $this->getFooterBlocks();
        return (bool)$footers[0];
    }

    public function getEncodedParams(array $params)
    {
        return $this->paramKeyHelper->getEncodedParams($params);
    }

    /**
     * return data for all blocks set for the footers
     * maximum 4
     *
     * @return array    array[0] contains how many blocks we need to set up
     * @access public
     */
    public function getFooterBlocks()
    {
        $storeId = $this->getStoreId();
        if (!isset($this->parameters[$storeId]['footers'])) {
            $this->parameters[$storeId]['footers'][0] = 0;
            for ($i = 1; $i < 5; $i++) {
                $this->parameters[$storeId]['footers'][$i] =
                    $this->_scopeConfig->getValue(
                        self::XML_PATH_FOOTER . $i,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                if (!empty($this->parameters[$storeId]['footers'][$i])) {
                    $this->parameters[$storeId]['footers'][0] = $i;
                }
            }
        }
        return $this->parameters[$storeId]['footers'];
    }

    /**
     * @return int
     */
    public function getMarginBetween()
    {
        return self::MARGIN_IN_BETWEEN;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        if ($this->hasFooter()) {
            $marginBetween = $this->getMarginBetween();
            $footers = $this->getFooterBlocks();
            $num = $footers[0];
            return (100 - ($num - 1) * $marginBetween) / $num;
        }

        return 100;
    }

    private function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getFooterPageWidth()
    {
        return $this->pageHelper->getPageWidth() - 2 * $this->pageHelper->getSideMargins();
    }

    public function getFooterXOffset()
    {
        return $this->pageHelper->getSideMargins();
    }
}
