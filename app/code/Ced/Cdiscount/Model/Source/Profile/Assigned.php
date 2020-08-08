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
 * @package   Ced_m2.1.9
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source\Profile;

class Assigned extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public $profileCollection;

    public function __construct(
        \Ced\Cdiscount\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        $this->profileCollection = $collectionFactory;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [
            [
                'label' => 'No Profile',
                'value' => ''
            ]
        ];
        $profile = $this->profileCollection->create();
        if ($profile->getSize() > 0) {
            foreach ($profile as $profileValue) {
                $data[] = [
                    'label' => $profileValue->getProfileName(),
                    'value' => $profileValue->getId()
                ];
            }
        }
        return $data;
    }
}
