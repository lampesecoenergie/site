<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Create pdf for shipments when using the setting print order as packing slip
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class OrderShipment extends Order
{
    const XML_PATH_TITLE = 'sales_pdf/shipment/shipmenttitle';
    const XML_PATH_ADDRESSES = 'sales_pdf/shipment/shipmentaddresses';
    const XML_PATH_COLUMNS = 'sales_pdf/shipment/columns';
    const XML_PATH_CUSTOMTEXT = 'sales_pdf/shipment/shipmentcustom';
    const XML_PATH_SORTBY = 'sales_pdf/shipment/sortby';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser_ordershipment';
    const PDF_TYPE = 'ordershipment';

    protected $integratedLabelsConfigPath = 'sales_pdf/shipment/shipmentintegratedlabels';

    /*
     * We need to translate the qty column to qtyOrdered
     * to pick up the right value for display
     */
    public function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $return = [];
        foreach ($columns as $column) {
            if ($column['index'] === 'qty') {
                $column['index'] = 'qtyOrdered';
            }
            $return[] = $column;
        }
        return $return;
    }

    /**
     * get main heading for shipment title ie PACKING SLIP
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function showOrderIncrementId()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Pdf\AbstractPdf::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getAddressesToDisplay()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ADDRESSES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getColumnConfig()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_COLUMNS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getCustomText()
    {
        return $this->processCustomVars(
            $this->_scopeConfig->getValue(
                self::XML_PATH_CUSTOMTEXT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            ),
            $this->getTemplateVars()
        );
    }

    protected function getSortColumnsBy()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_SORTBY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * We generally don't want to display subitems and items that would have a 0 effective qty
     *
     * @param $item
     *
     * @return bool
     */
    public function shouldDisplayItem($item)
    {

        if ($item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyRefunded() == 0) {
            return false;
        }
        return parent::shouldDisplayItem($item);
    }

    public function prepareItem($item)
    {
        $item = parent::prepareItem($item);
        $item->setQtyOrdered($item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyRefunded());
        return $item;
    }
}
