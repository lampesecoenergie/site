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
 * @category  Ced
 * @package   Ced_Wish
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Ui\Component\ProductForm;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Field;

class MultiShipping extends AbstractModifier
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory
     */
    public $parentFactory;

    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;

    /**
     * MultiShipping constructor.
     * @param LayoutFactory $layoutFactory
     * @param Registry $registry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $parentFactory
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        Registry $registry,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $parentFactory,
        \Magento\Framework\objectManagerInterface $_objectManager
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->parentFactory = $parentFactory;
        $this->_objectManager = $_objectManager;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return bool
     */
    public function getProduct()
    {
        $product = $this->registry->registry('current_product');
        if ($product && $product->getId()
            && $product->getWishProfileId()
            && $product->getWishProductId()
            && ($product->getTypeId() == 'simple' || $product->getTypeId() == 'configurable')
        ) {
            if ($product->getTypeId() == 'simple') {
                $parentIdS = $this->parentFactory->create()
                    ->getParentIdsByChild($product->getId());
                if (!empty($parentIdS)) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->dataHelper = $this->_objectManager->create('Ced\EbayMultiAccount\Helper\Data');
        $this->ebaymultiaccountHelper = $this->_objectManager->create('Ced\EbayMultiAccount\Helper\EbayMultiAccount');
        $this->multiAccountHelper = $this->_objectManager->create('Ced\EbayMultiAccount\Helper\MultiAccount');
        $accountId = 1;
        if ($this->registry->registry('ebay_account'))
            $this->registry->unregister('ebay_account');
        $this->multiAccountHelper->getAccountRegistry($accountId);
        $this->dataHelper->updateAccountVariable();
        $this->ebaymultiaccountHelper->updateAccountVariable();
        $options = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Config\DomesticShippingService')->toOptionArray();
        $advancePricing = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-tier-price',
                        'label' => __('Shipping Pricing'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'required' => false,
                        'sortOrder' => 20,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'service' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'service',
                                        'label' => __('Shipping Service'),
                                        'options' => $options,
                                        'value' => '',
                                        'visible' => true,
                                        'disabled' => false,
                                        'sortOrder' => 10,
                                    ],
                                ],
                            ],
                        ],
                        'charge' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Charges'),
                                        'dataScope' => 'charge',
                                        'sortOrder' => 20,
                                    ],
                                ],
                            ],
                        ],
                        'add_charge' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Additional Charges'),
                                        'dataScope' => 'add_charge',
                                        'sortOrder' => 30,
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                        'sortOrder' => 50,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->meta = $meta;
        $disableAttribute = ['ebay_domestic_shipping'];
        foreach ($this->meta['ebay']['children'] as $key => $value) {
            foreach ($value['children'] as $k => $val) {
                if (in_array($k, $disableAttribute)) {
                    if (isset($this->meta['ebay']['children'][$key]['children'][$k]['arguments']['data']['config'])) {
                        $this->meta['ebay']['children'][$key]['children'][$k] = $advancePricing;
                    }
                }
            }
        }
        return $this->meta;
    }
}
