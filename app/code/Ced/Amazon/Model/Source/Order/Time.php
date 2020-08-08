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

namespace Ced\Amazon\Model\Source\Order;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Time
 * @package Ced\Amazon\Model\Source\Order\Time
 */
class Time extends AbstractSource
{
    const LAST_ONE_HOUR = '-1 hours';
    const LAST_TWO_HOURS = '-2 hours';
    const LAST_FOUR_HOURS = '-4 hours';
    const LAST_EIGHT_HOURS = '-8 hours';
    const LAST_ONE_DAY = '-1 days';
    const LAST_TWO_DAYS = '-2 days';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::LAST_ONE_HOUR,
                'label' => __('-1 hours'),
            ],
            [
                'value' => self::LAST_TWO_HOURS,
                'label' => __('-2 hours'),
            ],
            [
                'value' => self::LAST_FOUR_HOURS,
                'label' => __('-4 hours'),
            ],
            [
                'value' => self::LAST_EIGHT_HOURS,
                'label' => __('-8 hours'),
            ],
            [
                'value' => self::LAST_ONE_DAY,
                'label' => __('-1 days'),
            ],
            [
                'value' => self::LAST_TWO_DAYS,
                'label' => __('-2 days'),
            ],
        ];
    }
}
