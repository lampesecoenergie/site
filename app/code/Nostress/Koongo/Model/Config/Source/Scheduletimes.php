<?php 
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */ 

/** 
* Config source for attribute schedule times
* 
* @category Nostress 
* @package Nostress_Koongo
* 
*/
namespace Nostress\Koongo\Model\Config\Source;

class Scheduletimes  extends \Nostress\Koongo\Model\Config\Source
{		
	const EVERY_15M = "every_15m";
	const EVERY_30M = "every_30m";
	const EVERY_60M = "every_60m";
	const EVERY_2H = "every_2h";
	const EVERY_4H = "every_4h";
	const EVERY_6H = "every_6h";
	const EVERY_12H = "every_12h";
	const EVERY_24H = "every_24h";
	
    public function toOptionArray()
    {
        return array(    
        			array('value'=> self::EVERY_24H, 'label'=> __("Once per day")),
        			array('value'=> self::EVERY_15M, 'label'=> __("Every 15 minutes")),
				    array('value'=> self::EVERY_30M, 'label'=> __("Every 30 minutes")),
				    array('value'=> self::EVERY_60M, 'label'=> __("Every hour")),
        			array('value'=> self::EVERY_2H, 'label'=> __("Every 2 hours")),
        			array('value'=> self::EVERY_4H, 'label'=> __("Every 4 hours")),
        			array('value'=> self::EVERY_6H, 'label'=> __("Every 6 hours")),
        			array('value'=> self::EVERY_12H, 'label'=> __("Every 12 hours"))        			     	        	       	
        );
    }   
}
?>
