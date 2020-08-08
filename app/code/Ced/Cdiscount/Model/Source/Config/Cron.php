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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source\Config;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class ServiceUrl
 * @package Ced\Cdiscount\Model\Source
 */
class Cron extends AbstractSource
{
    const CRON_CUSTOM = 'custom';
    const CRON_5MINUTES = '*/5 * * * *';
    const CRON_10MINUTES = '*/10 * * * *';
    const CRON_15MINUTES = '*/15 * * * *';
    const CRON_20MINUTES = '*/20 * * * *';
    const CRON_HALFHOURLY = '*/30 * * * *';
    const CRON_HOURLY = '0 * * * *';
    const CRON_2HOURLY = '0 */2 * * *';
    const CRON_DAILY = '0 0 * * *';
    const CRON_TWICEDAILY = '0 0,12 * * *';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $expressions = [
            [
                'label' => __('Every 5 Minutes'),
                'value' => self::CRON_5MINUTES
            ],
            [
                'label' => __('Every  10 Minute'),
                'value' => self::CRON_10MINUTES
            ],
            [
                'label' => __('Every  15 Minutes'),
                'value' => self::CRON_15MINUTES
            ],
            [
                'label' => __('Every 20 Minutes'),
                'value' => self::CRON_20MINUTES
            ],
            [
                'label' => __('Every Half Hour'),
                'value' => self::CRON_HALFHOURLY
            ],
            [
                'label' => __('Every Hour'),
                'value' => self::CRON_HOURLY
            ],
            [
                'label' => __('Every 2 Hours'),
                'value' => self::CRON_2HOURLY
            ],
            [
                'label' => __('Once A Day'),
                'value' => self::CRON_DAILY
            ],
            [
                'label' => __('Twice A Day'),
                'value' => self::CRON_TWICEDAILY
            ],
        ];

        return $expressions;
    }
}
