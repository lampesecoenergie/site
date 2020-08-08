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



namespace Mirasvit\Feed\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as RuleConditionCombine;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Feed\Helper\Output as OutputHelper;
use Mirasvit\Feed\Model\Rule\Condition\ProductFactory as ConditionProductFactory;

/**
 * @method $this setType($type)
 * @method string getType()
 */
class Combine extends RuleConditionCombine
{
    private $conditionProductFactory;

    private $outputHelper;

    public function __construct(
        ConditionProductFactory $conditionProductFactory,
        OutputHelper $outputHelper,
        Context $context
    ) {
        $this->conditionProductFactory = $conditionProductFactory;
        $this->outputHelper = $outputHelper;

        parent::__construct($context);

        $this->setType('Mirasvit\Feed\Model\Rule\Condition\Combine');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewChildSelectOptions()
    {
        $attributes = [];
        foreach ($this->getProductAttributes() as $code => $label) {
            $group = $this->outputHelper->getAttributeGroup($code);

            $attributes[(string)$group][] = [
                'value' => "Mirasvit\\Feed\\Model\\Rule\\Condition\\Product|$code",
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();

        $conditions = array_merge_recursive($conditions, [
            [
                'value' => 'Mirasvit\Feed\Model\Rule\Condition\Combine',
                'label' => __('Conditions Combination'),
            ],
        ]);

        foreach ($attributes as $group => $arrAttributes) {
            $conditions = array_merge_recursive($conditions, [
                [
                    'label' => $group,
                    'value' => $arrAttributes,
                ],
            ]);
        }

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * Product attributes
     *
     * @return array
     */
    protected function getProductAttributes()
    {
        $productCondition = $this->conditionProductFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();

        return $productAttributes;
    }
}
