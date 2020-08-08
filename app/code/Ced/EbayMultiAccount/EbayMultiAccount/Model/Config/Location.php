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
namespace Ced\EbayMultiAccount\Model\Config;

class Location implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [   'label'=>'US', 'value'=> '0' ],

            [   'label' => 'Canada',  'value' => 2 ],

            [   'label' => 'UK',  'value' => 3 ],

            [   'label'=>'Australia', 'value'=> 15 ],

            [   'label' => 'Austria',  'value' => 16 ],

            [   'label' => 'Belgium_French',  'value' => 23 ],

            [   'label' => 'France',  'value' => 71 ],

            [   'label' => 'Germany',  'value' => 77 ],

            [   'label' => 'Italy',  'value' => 101 ],

            [   'label' => 'Belgium_Dutch',  'value' => 123 ],

            [   'label' => 'Netherlands',  'value' => 146 ],

            [   'label' => 'Spain',  'value' => 186 ],

            [   'label' => 'Switzerland',  'value' => 193 ],
            
            [   'label' => 'HongKong',  'value' => 201 ],

            [   'label' => 'India',  'value' => 203],

            [   'label' => 'Ireland',  'value' => 205 ],

            [   'label'=>'Malaysia', 'value'=> 207 ],

            [   'label'=>'CanadaFrench', 'value'=> 210 ],
            
            [   'label' => 'Philippines',  'value' => 211 ],

            [   'label' => 'Poland',  'value' => 212 ],

            [   'label'=>'Singapore', 'value'=> 216 ]
        ];
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
