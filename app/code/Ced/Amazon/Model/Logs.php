<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Amazon Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

/**
 * @deprecated: use \Ced\Integrator\Helper\Logger for logging.
 * Class Logs
 * @package Ced\Amazon\Model
 */
class Logs extends \Magento\Framework\Model\AbstractModel
{
    const NAME = 'ced_amazon_log';

    /**
     * @return  void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Logs::class);
    }
}
