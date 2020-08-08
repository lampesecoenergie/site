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

namespace Ced\Amazon\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @deprecated
 * Class OrderFailed
 * @package Ced\Amazon\Model
 */
class OrderFailed extends AbstractModel
{
    const NAME = 'ced_amazon_failed_order';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\OrderFailed::class);
    }
}
