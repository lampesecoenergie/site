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

namespace Ced\Integrator\Model\Source\Log;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Level
 * @package Ced\Integrator\Model\Source\Log
 */
class Level extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        $levels = [
            [
                'value' => 0,
                'label' => __("No Muting"),
            ]
        ];
        foreach (\Ced\Integrator\Helper\Logger::$levels as $level) {
            $levels[] = [
                'value' => $level,
                'label' => __($level),
            ];
        }

        return $levels;
    }
}
