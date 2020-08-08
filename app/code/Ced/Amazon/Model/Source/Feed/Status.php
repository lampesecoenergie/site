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

namespace Ced\Amazon\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Ced\Amazon\Model\Source
 */
class Status extends AbstractSource
{
    const NOT_SUBMITTED = '_NOT_SUBMITTED_';
    const SUBMITTED = '_SUBMITTED_';
    const IN_PROGRESS = '_IN_PROGRESS_';
    const FAILED = '_FAILED_';
    const DONE = '_DONE_';
    const CANCELLED = '_CANCELLED_';
    const DONE_NO_DATA = '_DONE_NO_DATA_';
    const SYNCED = '_SYNCED_';

    const STATUS_LIST = [
        self::NOT_SUBMITTED,
        self::SUBMITTED,
        self::IN_PROGRESS,
        self::FAILED,
        self::DONE,
        self::SYNCED,
        self::DONE_NO_DATA,
        self::CANCELLED,
    ];

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NOT_SUBMITTED,
                'label' => __('Not Submitted'),
            ],
            [
                'value' => self::SUBMITTED,
                'label' => __('Submitted'),
            ],
            [
                'value' => self::IN_PROGRESS,
                'label' => __('In Progress'),
            ],
            [
                'value' => self::DONE,
                'label' => __('Done'),
            ],
            [
                'value' => self::DONE_NO_DATA,
                'label' => __('Done (No Data)'),
            ],
            [
                'value' => self::CANCELLED,
                'label' => __('Cancelled (No Data)'),
            ],
            [
                'value' => self::SYNCED,
                'label' => __('Synced'),
            ],
        ];
    }
}
