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

class ShipToLocation implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'Africa', 'label' =>'Africa'],

            ['value' => 'Americas', 'label' =>'Americas'],

            ['value' => 'Asia', 'label' =>'Asia'],

            ['value' => 'Caribbean', 'label' =>'Caribbean'],

            ['value' => 'CustomCode', 'label' =>'Reserved for internal or future use'],

            ['value' => 'Europe', 'label' =>'Europe'],

            ['value' => 'EuropeanUnion', 'label' =>' European Union'],

            ['value' => 'LatinAmerica','label' =>' Latin America'],

            ['value' => 'MiddleEast','label' =>'Middle East'],

            ['value' => 'None', 'label' =>'(description not yet available)'],

            ['value' => 'NorthAmerica', 'label' =>' North America'],

            ['value' => 'Oceania', 'label' =>' Oceania (Pacific region other than Asia)'],

            ['value' => 'SouthAmerica', 'label' =>'South America'],

            ['value' => 'WillNotShip ', 'label' =>' Seller will not ship the item.'],

            ['value' => 'Worldwide', 'label' =>'Seller has specified Worldwide']
        ];
   	}
}