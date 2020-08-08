<?php

namespace Fooman\PdfCustomiser\Model\ResourceModel\Order\Tax\Item;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    // phpcs:ignore PSR2.Methods.MethodDeclaration
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Tax\Item::class,
            \Magento\Sales\Model\ResourceModel\Order\Tax\Item::class
        );
    }

    // phpcs:ignore PSR2.Methods.MethodDeclaration
    public function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinRight(
            ['_sott' => $this->getTable('sales_order_tax')],
            'main_table.tax_id=_sott.tax_id',
            'code'
        );
    }

    public function getTaxItemsByItemId($item)
    {
        $this->addFieldToFilter('item_id', $item);

        return $this;
    }
}
