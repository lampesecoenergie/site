<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
 
namespace Mageants\BannerSlider\Model;
 
class Sliders extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->_init('Mageants\BannerSlider\Model\ResourceModel\Sliders');
    }
	
    /**
     *
     * @return default values for edit
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
		
    /**
     *
     * @return Slider option array
     */
    public function toOptionArray()
    {
		$sliders = $this->getCollection();
		
		$values[''] = "Select Slider" ;
		
		foreach($sliders as $slider)
		{
			$values[$slider->getId()] = $slider->getSliderName();
		}
		
        return $values;
    }
	
}
