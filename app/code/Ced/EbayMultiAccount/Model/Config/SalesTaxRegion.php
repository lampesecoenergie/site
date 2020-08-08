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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class SalesTaxRegion
 * @package Ced\EbayMultiAccount\Model\Config
 */
class SalesTaxRegion implements ArrayInterface
{
    /**
     * @return array
     */
	public function toOptionArray()
    {
       return [
            ['value'=>'', 'label'=>'--Please Select--'],
            ['value'=>'AB', 'label'=>'Alberta'],
            ['value'=>'BC', 'label'=>'British Columbia'],
            ['value'=>'MB', 'label'=>'Manitoba'],
            ['value'=>'NB', 'label'=>'New Brunswick'],
            ['value'=>'NL', 'label'=>'Newfoundland and Labrodor'],
            ['value'=>'NS', 'label'=>'Nova Scotia'],
            ['value' => 'NT', 'label' => 'Northwest Territories'],
            ['value'=>'NU', 'label'=>'Nunavut'],
            ['value'=>'ON', 'label'=>'Ontario'],
            ['value'=>'PE', 'label'=>'Prince Edward Island'],
            ['value'=>'QC', 'label'=>'Quebec'],
            ['value'=>'SK', 'label'=>'Saskatchewan'],
            ['value'=>'YT', 'label'=>'Yukon'],
            ['value' => 'AN', 'label' => 'Andaman and Nicobar Islands'],
            ['value'=>'AP', 'label'=>'Andhra Pradesh'],
            ['value'=>'AR', 'label'=>'Arunachal Pradesh'],
            ['value'=>'AS', 'label'=>'Assam'],
            ['value'=>'BR', 'label'=>'Bihar'],
            ['value'=>'CH', 'label'=>'Chandigarh'],
            ['value'=>'CT', 'label'=>'Chhattisgarh'],
            ['value' => 'DN', 'label' => 'Dadra and Nagar Haveli'],
            ['value'=>'DD', 'label'=>'Daman and Diu'],
            ['value'=>'DL', 'label'=>'Delhi'],
            ['value'=>'GA', 'label'=>'Goa'],
            ['value'=>'GJ', 'label'=>'Gujarat'],
            ['value'=>'HR', 'label'=>'Haryana'],
            ['value'=>'HP', 'label'=>'Himachal Pradesh'],
            ['value' => 'JK', 'label' => 'Jammu and Kashmir'],
            ['value'=>'JH', 'label'=>'Jharkhand'],
            ['value'=>'KA', 'label'=>'Karnataka'],
            ['value'=>'KL', 'label'=>'Kerala'],
            ['value'=>'LD', 'label'=>'Lakshadweep'],
            ['value'=>'MP', 'label'=>'Madhya Pradesh'],
            ['value'=>'MH', 'label'=>'Maharashtra'],
            ['value' => 'MN', 'label' => 'Manipur'],
            ['value'=>'ML', 'label'=>'Meghalaya'],
            ['value'=>'MZ', 'label'=>'Mizoram'],
            ['value'=>'NL', 'label'=>'Nagaland'],
            ['value'=>'OR', 'label'=>'Orissa'],
            ['value'=>'PY', 'label'=>'Pondicherry'],
            ['value'=>'PB', 'label'=>'Punjab'],
            ['value' => 'RJ', 'label' => 'Rajasthan'],
            ['value'=>'SK', 'label'=>'Sikkim'],
            ['value'=>'TN', 'label'=>'Tamil Nadu'],
            ['value'=>'TR', 'label'=>'Tripura'],
            ['value'=>'UL', 'label'=>'Uttaranchal'],
            ['value'=>'UP', 'label'=>'Uttar Pradesh'],
            ['value'=>'WB', 'label'=>'West Bengal'],
            ['value' => 'AL', 'label' => 'Alabama'],
            ['value'=>'AK', 'label'=>'Alaska'],
            ['value'=>'AZ', 'label'=>'Arizona'],
            ['value'=>'AR', 'label'=>'Arkansas'],
            ['value'=>'CA', 'label'=>'California'],
            ['value'=>'CO', 'label'=>'Colorado'],
            ['value'=>'CT', 'label'=>'Connecticut'],
            ['value' => 'DE', 'label' => 'Delaware'],
            ['value'=>'DC', 'label'=>'District of Columbia'],
            ['value'=>'FL', 'label'=>'Florida'],
            ['value'=>'GA', 'label'=>'Georgia'],
            ['value'=>'HI', 'label'=>'Hawaii'],
            ['value'=>'ID', 'label'=>'Idaho'],
            ['value' => 'IL', 'label' => 'Illinois'],
            ['value'=>'IN', 'label'=>'Indiana'],
            ['value'=>'IA', 'label'=>'Iowa'],
            ['value'=>'KS', 'label'=>'Kansas'],
            ['value'=>'KY', 'label'=>'Kentucky'],
            ['value'=>'LA', 'label'=>'Louisiana'],
            ['value'=>'ME', 'label'=>'Maine'],
            ['value' => 'MD', 'label' => 'Maryland'],
            ['value'=>'MA', 'label'=>'Massachusetts'],
            ['value'=>'MI', 'label'=>'Michigan'],
            ['value'=>'MN', 'label'=>'Minnesota'],
            ['value'=>'MS', 'label'=>'Mississippi'],
            ['value'=>'MO', 'label'=>'Missouri'],
            ['value'=>'MT', 'label'=>'Montana'],
            ['value' => 'NE', 'label' => 'Nebraska'],
            ['value'=>'NV', 'label'=>'Nevada'],
            ['value'=>'NH', 'label'=>'New Hampshire'],
            ['value'=>'NJ', 'label'=>'New Jersey'],
            ['value'=>'NM', 'label'=>'New Mexico'],
            ['value'=>'NY', 'label'=>'New York'],
            ['value'=>'NC', 'label'=>'North Carolina'],
            ['value' => 'ND', 'label' => 'North Dakota'],
            ['value'=>'OH', 'label'=>'Ohio'],
            ['value'=>'OK', 'label'=>'Oklahoma'],
            ['value'=>'OR', 'label'=>'Oregon'],
            ['value'=>'PA', 'label'=>'Pennsylvania'],
            ['value'=>'RI', 'label'=>'Rhode Island'],
            ['value'=>'SC', 'label'=>'South Carolina'],
            ['value' => 'SD', 'label' => 'South Dakota'],
            ['value'=>'TN', 'label'=>'Tennessee'],
            ['value'=>'TX', 'label'=>'Texas'],
            ['value'=>'UT', 'label'=>'Utah'],
            ['value'=>'VT', 'label'=>'Vermont'],
            ['value'=>'VA', 'label'=>'Virginia'],
            ['value'=>'WA', 'label'=>'Washington'],
            ['value' => 'WV', 'label' => 'West Virginia'],
            ['value'=>'WI', 'label'=>'Wisconsin'],
            ['value'=>'WY', 'label'=>'Wyoming']
       ];
    }
}
