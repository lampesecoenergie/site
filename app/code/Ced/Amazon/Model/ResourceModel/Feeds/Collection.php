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

namespace Ced\Amazon\Model\ResourceModel\Feeds;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @deprecated
 * Class Collection
 * @package Ced\Amazon\Model\ResourceModel\Feeds
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\Amazon\Model\Feeds', 'Ced\Amazon\Model\ResourceModel\Feeds');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
