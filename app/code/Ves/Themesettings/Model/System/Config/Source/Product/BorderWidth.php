<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Model\System\Config\Source\Product;

class BorderWidth implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
				['value' => '1px', 'label' => __('1px')],
				['value' => '2px', 'label' => __('2px')],
				['value' => '3px', 'label' => __('3px')],
				['value' => '4px', 'label' => __('4px')],
				['value' => '5px', 'label' => __('5px')],
				['value' => '6px', 'label' => __('6px')],
				['value' => '7px', 'label' => __('7px')],
				['value' => '8px', 'label' => __('8px')],
				['value' => '9px', 'label' => __('9px')],
				['value' => '10px', 'label' => __('10px')],
				['value' => '11px', 'label' => __('11px')],
				['value' => '12px', 'label' => __('12px')],
			];
	}
}
