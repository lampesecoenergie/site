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
namespace Ced\Cdiscount\Ui\Component\Profile\Form\Categories;


use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    public $category;

    public function __construct
    (
        \Ced\Cdiscount\Helper\Category $category
    )
    {
        $this->category = $category;
    }

    /**
     * @return array
     */

    public function toOptionArray()
    {
        $preparedArray = [];
        foreach ($this->category->getCategoriesTree() as $value) {
           $preparedArray[] = [ 'value' => $value['code'],
                                'label' => "{$value['name']} - [{$value['code']}]"
           ];
        }
        return $preparedArray;
    }
}