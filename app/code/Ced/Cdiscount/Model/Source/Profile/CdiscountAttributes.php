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
namespace Ced\Cdiscount\Model\Source\Profile;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CdiscountAttributes
 * @package Ced\Cdiscount\Model\Source\Profile
 * @deprecated
 */
class CdiscountAttributes implements OptionSourceInterface
{

    public $magentoAttributes;

    public $json;

    public $product;

    public $config;

    public $category;

    public $_coreRegistry;

    public $_cdiscountAttribute;
    /**
     * Get options
     *
     * @return array
     */

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $magentoAttributes,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Registry $registry,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Helper\Category $category,
        array $data = []
    )
    {
        $this->magentoAttributes = $magentoAttributes;
        $this->json = $json;
        $this->category = $category;
        $this->_coreRegistry = $registry;
    }

    public function toOptionArray()
    {
        return $this->getCdiscountAttributes();
    }

    /**
     * @return array
     */
    public function getCdiscountAttributes()
    {
        $attributes = [];

        // For Profile Saved
        $categoryIds = [10001963];

        $params = [
            'category_ids' => $categoryIds,
            'isMandatory' => 1
        ];
        $requiredAttributes = [];

        foreach ($this->category->getAttributes($params) as $item) {
            $requiredAttributes[] = [
                'value' => $item['name'],
                'label' => $item['label'],
                'options' => $item['options'],
                'leaf' => true
            ];
        }

        $params = [
            'category_ids' => $categoryIds,
            'isMandatory' => 0
        ];
        $optionalAttributes = [];


        foreach ($this->category->getAttributes($params) as $item) {
            $optionalAttributes[] = [
                'value' => $item['name'],
                'label' => $item['label'],
                'options' => $item['options'],
                'leaf' => true
            ];
        }

        $attributes[] = array(
            'label' => __('Required Attributes'),
            'leaf' => false,
            'value' => 'required',
            'optgroup' => $requiredAttributes
        );


        $attributes[] = array(
            'label' => __('Optional Attributes'),
            'value' => 'optional',
            'leaf' => false,
            'optgroup' => $optionalAttributes
        );

        return $attributes;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

}