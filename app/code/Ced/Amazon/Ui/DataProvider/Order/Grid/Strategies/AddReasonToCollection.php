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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Order\Grid\Strategies;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddReasonToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if ($field == "reason") {
            $value = [];
            if (isset($condition['eq']) && !empty($condition['eq'])) {
                $value = [$condition['eq']];
            } elseif (isset($condition['in']) && !empty($condition['in'])) {
                $value = $condition['in'];
            }

            if (!empty($value) && is_array($value)) {
                /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $collection */
                $query = [];
                foreach ($value as $code) {
                    if ($code == \Ced\Amazon\Model\Source\Order\Failure\Reason::NO_ERROR) {
                        $query[] = ['like' => "%[]%"];
                        $query[] = ['null' => true];
                    } else {
                        $query[] = ['like' => "%" . $code . "%"];
                    }
                }

                $collection->addFieldToFilter("reason", $query);
            }
        }
    }
}
