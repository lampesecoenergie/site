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
* Config source for dropdown menu "Category Lowest Level"
* 
* @category Nostress 
* @package Nostress_Koongo
* 
*/
namespace Nostress\Koongo\Model\Config\Source;

class Categorylevel  extends \Nostress\Koongo\Model\Config\Source
{
    public function toOptionArray()
    {    	
        return array(
            array('value'=>'1', 'label'=> '1'),
            array('value'=>'2', 'label'=> '2'),
            array('value'=>'3', 'label'=> '3'),  
            array('value'=>'4', 'label'=> '4'),
            array('value'=>'5', 'label'=> '5'),
            array('value'=>'6', 'label'=> '6'),
            array('value'=>'7', 'label'=> '7'), 
        	array('value'=>'8', 'label'=> '8'),
        	array('value'=>'9', 'label'=> '9'),
        	array('value'=>'10', 'label'=> '10'),
        );
    }
}
?>
