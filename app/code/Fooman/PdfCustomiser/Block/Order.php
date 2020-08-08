<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Create pdf for orders
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Order extends AbstractSalesDocument
{
    const XML_PATH_TITLE = 'sales_pdf/order/ordertitle';
    const XML_PATH_ADDRESSES = 'sales_pdf/order/orderaddresses';
    const XML_PATH_COLUMNS = 'sales_pdf/order/columns';
    const XML_PATH_CUSTOMTEXT = 'sales_pdf/order/ordercustom';
    const XML_PATH_SORTBY = 'sales_pdf/order/sortby';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser_order';
    const PDF_TYPE = 'order';

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    /**
     * return array of variables to be passed to the template
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return array_merge(
            parent::getTemplateVars(),
            ['order' => $this->getOrder()]
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getSalesObject()
    {
        return $this->getOrder();
    }

    /**
     * get visible order items
     * overridden as property different for orders
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVisibleItems()
    {
        $items = [];
        $allItems = $this->getSalesObject()->getItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                if ($this->shouldDisplayItem($item)) {
                    $items[] = $this->prepareItem($item);
                }
            }
        }
        if ($this->getSortColumnsBy()) {
            uasort($items, [$this, 'sort']);
        }

        return $items;
    }

    /**
     * We generally don't want to display subitems
     *
     * @param $item
     *
     * @return bool
     */
    public function shouldDisplayItem($item)
    {
        return !$item->getParentItemId();
    }

    /**
     * Remove some fields on bundles
     *
     * @param $item
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareItem($item)
    {
        $this->addProductAttributeValues($item, $item);
        if ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE
            && $item->isChildrenCalculated()) {
            $item->unsPrice();
            $item->unsOriginalPrice();
            $item->unsRowTotal();
            $item->unsRowTotalInclTax();
            $item->unsTaxAmount();
        }
        return $item;
    }

    /**
     * get main heading for order title ie ORDER CONFIRMATION
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
}
