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

namespace Ced\Amazon\Model\Source\Account;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Mode
 * @package Ced\Amazon\Model\Source\Account\Mode
 */
class Mode extends AbstractSource
{
    const MODE_LIVE = 'live';
    const MODE_MOCK = 'mock';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::MODE_LIVE,
                'label' => __('Live'),
            ],
            [
                'value' => self::MODE_MOCK,
                'label' => __('Mock'),
            ],
        ];
    }
}
