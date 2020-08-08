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

namespace Ced\Amazon\Model\Source\Profile;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Category extends AbstractSource
{
    /** @var \Amazon\Sdk\Product\Category\Collection  */
    public $category;

    public function __construct(\Amazon\Sdk\Product\Category\Collection $collection)
    {
        $this->category = $collection;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $categories = [];
        /** @var array $categories */
        $root = $this->category->getCategories(0);
        $childs = $this->category->getCategories(1);
        foreach ($root as $item) {
            $subcategories = [];
            foreach ($childs as $sub) {
                if (isset($sub['parent']) && $sub['parent'] == $item['value']) {
                    $subcategories[] = [
                        'label' => $sub['label'],
                        'value' =>  $item['value'].'_'.$sub['value'],
                        'leaf' => true,
                        'optgroup' => []
                    ];
                }
            }

            $categories[] = [
                'label' => $item['label'],
                'value' => $item['value'],
                'optgroup' => $subcategories
            ];
        }

        return $categories;
    }
}
