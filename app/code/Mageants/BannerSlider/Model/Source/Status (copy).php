<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */

namespace Mageants\BannerSlider\Model\Source;

/**
 * Class Status
 * @package Mageants\BannerSlider\Model\Source
 */
class SlideType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * 	Category values
     */
    const SLIDE_CATEGORY = 2;
	
    /**
     * 	Product values
     */
    const SLIDE_PRODUCT = 1;
	
    /**
     * Image values
     */
    const SLIDE_IMAGE = 0;

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
        return [
            ['value' => self::SLIDE_IMAGE,  'label' => __('From Image')],
            ['value' => self::SLIDE_PRODUCT,  'label' => __('From Product')],
            ['value' => self::SLIDE_CATEGORY,  'label' => __('From Categories')],
        ];
    }
}
