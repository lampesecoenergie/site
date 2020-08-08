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
class Conditions extends AbstractSource
{
    const NEUF = 6;
    const RECONDITIONED = 5;
    const ETAT = 4;
    const BON = 3;
    const TRES = 2;
    const COMME = 1;


    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NEUF,
                'label' => __('Neuf - Neuf'),
            ],
            [
                'value' => self::RECONDITIONED,
                'label' => __('Neuf - Reconditionné'),
            ],
            [
                'value' => self::ETAT,
                'label' => __('Occasion - Etat Correct'),
            ],
            [
                'value' => self::BON,
                'label' => __('Occasion - Bon état'),
            ],
            [
                'value' => self::TRES,
                'label' => __('Occasion - Très bon état'),
            ],
            [
                'value' => self::COMME,
                'label' => __('Occasion - Comme neuf')
            ]

        ];
    }
}
