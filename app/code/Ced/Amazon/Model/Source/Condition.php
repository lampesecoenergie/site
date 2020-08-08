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
namespace Ced\Amazon\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Condition
 * @package Ced\Amazon\Model\Source
 */
class Condition extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [

            [
                'value' => 'New',
                'label' => __('New'),
            ],
            [
                'value' => 'UsedLikeNew',
                'label' => 'Used Like New',
            ],
            [
                'value' => 'UsedVeryGood',
                'label' => __('Used Very Good'),
            ],
            [
                'value' => 'Refurbished',
                'label' => __('Refurbished'),
            ],
            [
                'value' => 'UsedGood',
                'label' => __('Used Good'),
            ],
            [
                'value' => 'UsedAcceptable',
                'label' => __('Used Acceptable'),
            ],
            [
                'value' => 'CollectibleLikeNew',
                'label' => 'Collectible Like New',
            ],
            [
                'value' => 'CollectibleVeryGood',
                'label' => __('CollectibleVeryGood'),
            ],
            [
                'value' => 'CollectibleGood',
                'label' => __('Collectible Good'),
            ],
            [
                'value' => 'CollectibleAcceptable',
                'label' => __('Collectible Acceptable'),
            ],
            [
                'value' => 'Club',
                'label' => __('Club')
            ]
        ];
    }
}
