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
* Config source for schedule days
* 
* @category Nostress 
* @package Nostress_Koongo
* 
*/
namespace Nostress\Koongo\Model\Config\Source;

class Scheduledays  extends \Nostress\Koongo\Model\Config\Source
{	
	const EVERY_DAY = "everyday";
	const EVERY_WORKDAY = "everyworkday";
	const EVERY_WEEKENDDAY = "everyweekendday";
	const MONDAY = 1;	
	const TUESDAY = 2;
	const WEDNESDAY = 3;
	const THURSDAY = 4;
	const FRIDAY = 5;
	const SATURDAY = 6;
	const SUNDAY = 7;
	
    public function toOptionArray()
    {
        return array(
        			array('value'=> self::EVERY_DAY, 'label'=> __("Daily")),
        			array('value'=> self::EVERY_WORKDAY, 'label'=> __("Every workday")),
        			array('value'=> self::EVERY_WEEKENDDAY, 'label'=> __("Every day on weekend")),
	        		array('value'=> self::MONDAY, 'label'=> __("Every Monday")),
	        		array('value'=> self::TUESDAY, 'label'=> __("Every Tuesday")),
	        		array('value'=> self::WEDNESDAY, 'label'=> __("Every Wednesday")),
	        		array('value'=> self::THURSDAY, 'label'=> __("Every Thursday")),
	        		array('value'=> self::FRIDAY, 'label'=> __("Every Friday")),
	        		array('value'=> self::SATURDAY, 'label'=> __("Every Saturday")),
	        		array('value'=> self::SUNDAY, 'label'=> __("Every Sunday"))
    	);        		
    }   
}
?>
