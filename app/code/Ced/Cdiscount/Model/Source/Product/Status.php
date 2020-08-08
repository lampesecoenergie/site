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

namespace Ced\Cdiscount\Model\Source\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package Ced\Cdiscount\Model\Source
 */
class Status extends AbstractSource
{
    const NOT_UPLOADED = 'NOT_UPLOADED';
    const UPLOADED = 'UPLOADED';
    const LIVE = 'LIVE';
    const INVALID = 'INVALID';
    const SUBMITTED = 'SUBMITTED';
    const PARTIALLY_LIVE = 'PARTIALLY_LIVE';

    const STATUS = [
        self::NOT_UPLOADED,
        self::INVALID,
        self::UPLOADED,
        self::LIVE,
        self::SUBMITTED,
        self::PARTIALLY_LIVE
    ];

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NOT_UPLOADED,
                'label' => __('Not Uploaded'),
            ],
            [
                'value' => self::UPLOADED,
                'label' => __('Uploaded'),
            ],
            [
                'value' => self::INVALID,
                'label' => __('Invalid'),
            ],
            [
                'value' => self::PARTIALLY_LIVE,
                'label' => __('Partially Live'),
            ],
            [
                'value' => self::LIVE,
                'label' => __('Live'),
            ],
            [
                'value' => self::SUBMITTED,
                'label' => __('Submitted')
            ]

        ];
    }
}
