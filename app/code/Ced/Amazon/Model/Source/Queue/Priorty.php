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
 * Class Priorty
 * @package Ced\Amazon\Model\Source
 */
class Priorty extends AbstractSource
{
    const HIGH = 'HIGH';
    const MEDIUM = 'MEDIUM';
    const LOW = 'LOW';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::HIGH,
                'label' => __('High'),
            ],
            [
                'value' => self::MEDIUM,
                'label' => __('Medium'),
            ],
            [
                'value' => self::LOW,
                'label' => __('Low'),
            ],
        ];
    }
}
