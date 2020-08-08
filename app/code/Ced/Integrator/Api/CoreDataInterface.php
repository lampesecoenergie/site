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
 * @category    Ced
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Api;

/**
 * Integrator Api Interface
 * @api
 * @since 100.0.2
 */
interface CoreDataInterface
{
    /**
     * Save Server Token in Magento.
     *
     * This call save the server token in Magento Config.
     *
     * @return mixed
     */
    public function saveToken();

}
