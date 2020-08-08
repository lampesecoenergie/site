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
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Logging implements ArrayInterface
{
    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => \Ced\Integrator\Helper\Logger::DEBUG,
                'label' => __('Debug'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::INFO,
                'label' => __('Info'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::NOTICE,
                'label' => __('Notice'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::WARNING,
                'label' => __('Warning'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::ERROR,
                'label' => __('Error'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::CRITICAL,
                'label' => __('Critical'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::ALERT,
                'label' => __('Alert'),
            ],
            [
                'value' => \Ced\Integrator\Helper\Logger::EMERGENCY,
                'label' => __('Emergency'),
            ],
        ];
    }
}
