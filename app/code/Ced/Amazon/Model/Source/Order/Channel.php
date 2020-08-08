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
 * Class Channel
 * @package Ced\Amazon\Model\Source\Order\Channel
 */
class Channel extends AbstractSource
{
    const TYPE_ALL = 'All';
    const TYPE_AFN = 'AFN';
    const TYPE_MFN = 'MFN';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::TYPE_ALL,
                'label' => __('Default (All)'),
            ],
            [
                'value' => self::TYPE_AFN,
                'label' => __('Amazon Fulfilled Network (AFN)'),
            ],
            [
                'value' => self::TYPE_MFN,
                'label' => __('Merchant Fulfilled Network (MFN)'),
            ],
        ];
    }
}
