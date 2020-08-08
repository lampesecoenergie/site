<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Ced\Amazon\Model\ResourceModel\Order
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Order::class,
            \Ced\Amazon\Model\ResourceModel\Order::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function _initSelect()
    {
        $this->addFilterToMap(
            'magento_status',
            'sales.status'
        );
        $this->addFilterToMap(
            'status',
            'main_table.status'
        );
        parent::_initSelect();
    }
}
