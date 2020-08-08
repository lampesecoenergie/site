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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Model\Source;

class Caution extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
    * @return array
    */
    public function getAllOptions()
    {
 		return [
            [
                'value' => '',
                'label' => __('--- Please Select Caution Type ----'),
            ],
 			[
				'label' =>'no warning applicable',
				'value' => 'no warning applicable',
			],
			[
				'label' =>'choking hazard small parts',
				'value' => 'choking hazard small parts',
			],
			[
				'label' =>'choking hazard is a small ball',
				'value' => 'choking hazard is a small ball',
			],
			[
				'label' =>'choking hazard is a marble',
				'value' => 'choking hazard is a marble',
			],
			[
				'label' =>'choking hazard contains a small ball',
				'value' => 'choking hazard contains a small ball',
			],
			[
				'label' =>'choking hazard contains a marble',
				'value' => 'choking hazard contains a marble',
			],
			[
				'label' =>'choking hazard balloon',
				'value' => 'choking hazard balloon',
			]
        ];
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }

    /**
     * Get EbayMultiAccount caution array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get EbayMultiAccount caution labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get EbayMultiAccount caution
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
