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

/**
 * Class ListingDuration
 * @package Ced\EbayMultiAccount\Model\Source
 */
class ListingDuration extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
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
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'label' => 'GTC',
                'value' =>'GTC'
            ],
            [
                'label' =>'Days_1',
                'value' => 'Days_1'
            ],
            [
                'label' => 'Days_10',
                'value' => 'Days_10'
            ],
            [
                'label' => 'Days_120',
                'value' => 'Days_120'
            ],
            [
                'label' => 'Days_21',
                'value' => 'Days_21'
            ],
            [
                'label' => 'Days_3',
                'value' => 'Days_3'
            ],
            [
                'label' => 'Days_30',
                'value' => 'Days_30'
            ],
            [
                'label' => 'Days_7',
                'value' => 'Days_7'
            ],
            [
                'label' => 'Days_90',
                'value' => 'Days_90'
            ],
            [
                'label' => 'Days_60',
                'value' => 'Days_60'
            ],
            [
                'label' => 'Days_5',
                'value' => 'Days_5'
            ]
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
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
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getLabel($options = [])
    {
        foreach ($this->getAllOptions() as $option) {
            $options[] =(string)$option['label'];
        }
        return $options;
    }
}
