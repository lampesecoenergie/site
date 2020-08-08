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

namespace Ced\Amazon\Model\Source\Queue;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Ced\Amazon\Model\Source
 */
class Status extends AbstractSource
{
    const SUBMITTED = '_SUBMITTED_'; // Given for processing.
    const IN_PROGRESS = '_IN_PROGRESS_'; // Generating feed.
    const PROCESSED = '_PROCESSED_'; //Feed prepared and sent.
    const DONE = '_DONE_'; // Feed processed and report fetched.
    const ERROR = '_ERROR_'; // Feed failed.

    const STATUS_LIST = [
        self::SUBMITTED,
        self::IN_PROGRESS,
        self::PROCESSED,
        self::DONE,
        self::ERROR,
    ];

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::SUBMITTED,
                'label' => __('Submitted'),
            ],
            [
                'value' => self::IN_PROGRESS,
                'label' => __('In Progress'),
            ],
            [
                'value' => self::PROCESSED,
                'label' => __('Processed'),
            ],
            [
                'value' => self::DONE,
                'label' => __('Done'),
            ],
            [
                'value' => self::ERROR,
                'label' => __('Error'),
            ],
        ];
    }
}
