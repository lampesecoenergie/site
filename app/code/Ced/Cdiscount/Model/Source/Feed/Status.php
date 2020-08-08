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

namespace Ced\Cdiscount\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package Ced\Cdiscount\Model\Source
 */
class Status extends AbstractSource
{
    const SUCCESS = 'Submitted';
    const FAILURE = 'Rejected';
    const INTEGRATED = 'Integrated';
    const INTEGRATION_PENDING = 'IntegrationPending';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::FAILURE,
                'label' => __('Rejected'),
            ],
            [
                'value' => self::SUCCESS,
                'label' => __('Submitted'),
            ],
            [
                'value' => self::INTEGRATED,
                'label' => __('Integrated'),
            ],
            [
                'value' => self::INTEGRATION_PENDING,
                'label' => __('IntegrationPending'),
            ],

        ];
    }
}
