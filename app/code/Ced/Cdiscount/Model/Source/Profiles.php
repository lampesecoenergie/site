<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Cdiscount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MagentoCategoryMapping
 * @package Ced\Cdiscount\Model\Source\Profile
 */
class Profiles implements OptionSourceInterface
{

    public $profile;

    public function __construct(
        \Ced\Cdiscount\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    )
    {
        $this->profile = $collectionFactory;
    }

    public function toOptionArray()
    {
        $profiles = $this->profile->create()
            ->addFieldToSelect(['id', 'profile_name']);
        $options = [
            [
                'label' => 'Not Assigned',
                'value' => '0'
            ]
        ];
        foreach ($profiles as $profile) {
            $option['label'] = $profile->getProfileName() . " [{$profile->getId()}]";
            $option['value'] = $profile->getId();
            $options[] = $option;
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }

        return $options;
    }

}