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

namespace Ced\Amazon\Model\Source;

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
                'value' => 0,
                'label' => __('No Muting'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::DEBUG,
                'label' => __('Debug'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::INFO,
                'label' => __('Info'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::NOTICE,
                'label' => __('Notice'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::WARNING,
                'label' => __('Warning'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::ERROR,
                'label' => __('Error'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::CRITICAL,
                'label' => __('Critical'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::ALERT,
                'label' => __('Alert'),
            ],
            [
                'value' => \Ced\Amazon\Helper\Logger::EMERGENCY,
                'label' => __('Emergency'),
            ],
        ];
    }
}
