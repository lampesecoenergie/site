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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package Ced\RueDuCommerce\Model\Source
 */
class Status extends AbstractSource
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const SUBMITTED = 'Submitted';
    const COMPLETE = 'COMPLETE';
    const SENT = 'SENT';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::FAILURE,
                'label' => __(self::FAILURE),
            ],
            [
                'value' => self::SUBMITTED,
                'label' => __(self::SUBMITTED),
            ],
            [
                'value' => self::SUCCESS,
                'label' => __(self::SUCCESS),
            ],
            [
                'value' => self::SENT,
                'label' => __(self::SENT),
            ],
            [
                'value' => self::COMPLETE,
                'label' => __(self::COMPLETE),
            ]
        ];
    }
}
