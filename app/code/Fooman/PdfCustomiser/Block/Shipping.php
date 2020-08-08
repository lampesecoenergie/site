<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Block in pdf to display shipping information
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shipping extends \Fooman\PdfCore\Block\Pdf\Block
{
    const XML_PATH_PRINT_SHIPPING_BARCODE = 'sales_pdf/all/allprinttrackingbarcode';
    const XML_PATH_DISPLAY_WEIGHT = 'sales_pdf/all/alldisplayweight';

    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/shipping.phtml';

    protected $tracks;
    protected $description;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fooman\PdfCore\Helper\ParamKey                  $paramKeyHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $tracks
     *
     * @return $this
     */
    public function setTracks(\Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $tracks)
    {
        $this->tracks = $tracks;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param string $desc
     *
     * @return $this
     */
    public function setShippingDescription($desc)
    {
        $this->description = $desc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShippingDescription()
    {
        return $this->description;
    }

    /**
     * should we print a barcode of the tracking number?
     *
     * @return bool
     * @access public
     */
    public function shouldPrintTrackingBarcode()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRINT_SHIPPING_BARCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getEncodedParams(array $params)
    {
        return $this->paramKeyHelper->getEncodedParams($params);
    }

    public function getTotalWeight()
    {
        $totalWeight = 0;

        $allItems = $this->getSalesObject()->getAllItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                if (!$item->getParentItemId()) {
                    if ($item->getRowWeight()) {
                        $totalWeight += $item->getRowWeight();
                    } elseif ($item->getWeight()) {
                        $totalWeight += $item->getQty() * $item->getWeight();
                    } else {
                        $totalWeight += $item->getQty() * $item->getOrderItem()->getWeight();
                    }
                }
            }
        }

        return $totalWeight;
    }

    public function getTotalOrderWeight()
    {
        $totalWeight = 0;

        $allItems = $this->getOrder()->getAllVisibleItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                $totalWeight += $item->getRowWeight();
            }
        }

        return $totalWeight;
    }

    public function getUnit()
    {
        return $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getSalesObject()->getStoreId()
        );
    }

    public function shouldDisplayWeight()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_WEIGHT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getSalesObject()->getStoreId()
        );
    }
}
