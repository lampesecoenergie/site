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
 * Class OperationType
 * @package Ced\Amazon\Model\Source
 */
class OperationType extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE,
                'label' => __('Update'),
            ],
            [
                'value' => \Amazon\Sdk\Base::OPERATION_TYPE_PARTIAL_UPDATE,
                'label' => __('PartialUpdate'),
            ],
            [
                'value' => \Amazon\Sdk\Base::OPERATION_TYPE_DELETE,
                'label' => __('Delete'),
            ],
            [
                'value' =>  \Amazon\Sdk\Base::OPERATION_TYPE_GET,
                'label' => __('Get'),
            ],
            [
                'value' =>  \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST,
                'label' => __('Request'),
            ],
        ];
    }
}
