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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source;

/**
 * Class Profile
 * @package Ced\Amazon\Model\Source
 */
class Profile extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public $profile;

    public $options;

    public function __construct(
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        $this->profile = $collectionFactory;
    }

    public function getAllOptions()
    {
        if (!isset($this->options)) {
            /** @var \Ced\Amazon\Model\ResourceModel\Profile\Collection $profiles */
            $profiles = $this->profile->create()->addFieldToSelect([
                \Ced\Amazon\Model\Profile::COLUMN_ID,
                \Ced\Amazon\Model\Profile::COLUMN_NAME
            ]);

            $this->options = [
                [
                    'label' => '  -  ',
                    'value' => '',
                ]
            ];

            /** @var \Ced\Amazon\Model\Profile $profile */
            foreach ($profiles as $profile) {
                $option['label'] = $profile->getId() . " | " . $profile->getData(\Ced\Amazon\Model\Profile::COLUMN_NAME);
                $option['value'] = $profile->getId();
                $this->options[] = $option;
            }
        }

        return $this->options;
    }
}
