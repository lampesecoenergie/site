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

use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class Category
 * @package Ced\Amazon\Model\Source
 * @deprecated : Use Core Magento Class
 */
class Category extends \Magento\Catalog\Ui\Component\Product\Form\Categories\Options
{
    public $allowedLevels = [1, 2, 3];

    public $escaper;

    public $options = [];

    public $mapping = [];

    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($categoryCollectionFactory, $request);
        $this->escaper = $escaper;
    }

    public function toOptionArray()
    {
        $tree = $this->getCategoriesTree();
        foreach ($tree as $option) {
            $sub = $option['value'];
            if (isset($option['optgroup'])) {
                $sub = [];
                foreach ($option['optgroup'] as $level1) {
                    $last = $level1['value'];
                    if (isset($level1['optgroup'])) {
                        $last = [];
                        foreach ($level1['optgroup'] as $level2) {
                            $label = $this->escaper->escapeHtml($level2['label']);
                            $this->mapping[$level2['value']] = $label;
                            $last[] = [
                                'label' => str_repeat(' ', 8) . $label,
                                'value' => $level2['value']
                            ];
                        }
                    }

                    $label = $this->escaper->escapeHtml($level1['label']);
                    if (is_array($last)) {
                        $sub[] = [
                            'label' => $label,
                            'value' => $level1['value']
                        ];
                        $this->mapping[$level1['value']] = $label;
                    }
                    $sub[] = [
                        'label' => str_repeat(' ', 4) .$label,
                        'value' => $last
                    ];
                }
            }

            $label = $this->escaper->escapeHtml($option['label']);

            if (is_array($sub)) {
                $this->options[] = [
                    'label' => $label,
                    'value' => $option['value']
                ];

                $this->mapping[$option['value']] = $label;
            }

            $this->options[] = [
                'label' => $label,
                'value' => $sub
            ];
        }
        return $this->options;
    }

    public function getCategoryNames($ids)
    {
        $names = [];

        if (empty($this->mapping)) {
            $this->toOptionArray();
        }

        foreach ($this->mapping as $id => $name) {
            if (in_array($id, $ids)) {
                $names[] = $name;
            }
        }

        return $names;
    }
}
