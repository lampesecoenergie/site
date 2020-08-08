<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\Dynamic;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Feed\Export\Liquid\Context as LiquidContext;
use Mirasvit\Feed\Export\Liquid\Template as LiquidTemplate;

/**
 * @method string getName()
 * @method string getCode()
 * @method array getConditions()
 */
class Attribute extends AbstractModel
{
    /**
     * @var Attribute\Validator
     */
    protected $validator;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Context                $context
     * @param Registry               $registry
     * @param Attribute\Validator    $validator
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Attribute\Validator $validator,
        ObjectManagerInterface $objectManager
    ) {
        $this->validator = $validator;
        $this->objectManager = $objectManager;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Feed\Model\ResourceModel\Dynamic\Attribute');
    }

    /**
     * @param \Magento\Catalog\Model\Product                 $product
     * @param \Mirasvit\Feed\Export\Resolver\ProductResolver $resolver
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValue($product, $resolver)
    {
        if (!is_array($this->getConditions())) {
            return false;
        }

        foreach ($this->getConditions() as $condition) {
            $valid = true;

            if (isset($condition['statement'])) {
                foreach ($condition['statement'] as $statement) {
                    if ($statement['attribute'] == 'qty' || $statement['attribute'] == 'is_in_stock') {
                        $attrValue = $product['quantity_and_stock_status'][$statement['attribute']];
                    } else {
                        $attrValue = $product->getDataUsingMethod($statement['attribute']);
                    }

                    if (!$attrValue) {
                        $attrValue = $product->getData($statement['attribute']);
                    }

                    if (!$attrValue) {
                        $attrValue = $this->getLiquidValue(
                            $resolver,
                            '{{ product.' . $statement['attribute'] . ' }}',
                            ['product' => $product]
                        );
                    }

                    if (is_scalar($attrValue)) {
                        $attrValue = trim($attrValue);
                    }

                    $this->validator->setOperator($statement['operator'])
                        ->setValue($statement['value'])
                        ->setData('value_parsed', $statement['value']);

                    if (in_array($statement['operator'], ['()', '!()'])) {
                        $attrValue = explode(',', $attrValue);
                        $attrValue = array_map('trim', $attrValue);
                    }

                    if (!$this->validator->validateAttribute($attrValue)) {
                        $valid = false;
                    }
                }
            }

            if ($valid) {
                $type = $condition['result']['type'];
                $value = $condition['result']['value'];

                if ($value == '0') {
                    return $value;
                }

                $pattern = '';
                if ($type == 'pattern') {
                    $pattern = $value;
                } elseif ($type == 'parent') {
                    $pattern = '{{ product.parent.' . $value . ' }}';
                } elseif ($type == 'only_parent' || $type == 'grouped') {
                    $pattern = '{{ product.only_parent.' . $value . ' }}';
                } else {
                    $pattern = '{{ product.' . $value . ' }}';
                }

                return $this->getLiquidValue(
                    $resolver,
                    $pattern,
                    ['product' => $product]
                );

            }
        }

        return false;
    }

    /**
     * @param \Mirasvit\Feed\Export\Resolver\ProductResolver $resolver
     * @param string                                         $pattern
     * @param array                                          $vars
     * @return string
     */
    public function getLiquidValue($resolver, $pattern, $vars)
    {
        $liquidTemplate = new LiquidTemplate();
        $liquidTemplate->parse($pattern);

        $liquidContext = new LiquidContext($resolver, $vars);

        $liquidContext->addFilters($this->objectManager->get('\Mirasvit\Feed\Export\Filter\Pool')->getScopes());
        $result = $liquidTemplate->execute($liquidContext);

        return $result;
    }

    /**
     * @return array
     */
    public function getRowsToExport()
    {
        $array = [
            'name',
            'code',
            'conditions_serialized',
            'conditions'
        ];

        return $array;
    }
}
