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
 * Class Status
 * @package Ced\Amazon\Model\Source
 */
class Status extends AbstractSource
{
    const ADDED = 'ADDED';
    const INVALID = 'INVALID';
    const VALID = 'VALID';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::ADDED,
                'label' => __('Added'),
            ],
            [
                'value' => self::INVALID,
                'label' => __('Invalid'),
            ],
            [
                'value' => self::VALID,
                'label' => __('Valid'),
            ]
        ];
    }
}
