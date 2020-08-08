<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */

namespace Mageants\BannerSlider\Model\Source;

use \Mageants\BannerSlider\Model\SlidersFactory;

/**
 * Class Status
 * @package Mageants\BannerSlider\Model\Source
 */
class SlidersSelect implements \Magento\Framework\Data\OptionSourceInterface
{
	/**
     * @var _slidersFactory
     */
	protected $_slidersFactory;
	/**
	 * @param SlidersFactory $slidersFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
	public function __construct(
		SlidersFactory $slidersFactory		
	) {	
		//parent::__construct();
		
	 	$this->_slidersFactory = $slidersFactory;		
	}
    /**
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = ['' => ' '];
		
        foreach ($this->toOptionArray() as $option) 
		{
            $optionArray[$option['value']] = $option['label'];
        }
		
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
		$sliders=array();
		$sliderFactory = $this->_slidersFactory->create();
		
		$slidersCollection = $sliderFactory->getCollection();
		
		foreach($slidersCollection as $key=>$slider)
		{
			$sliders[$key]['value'] = $slider->getId();
			
			$sliders[$key]['label'] = $slider->getSliderName();
		}
		
        return $sliders;
    }
}
