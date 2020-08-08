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
namespace Ves\Themesettings\Model\System\Config\Source\Product\Tabs;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
    	//Important: note the order of values - "Tabs" moved to first position
		return [
				['value' => 'tabs', 'label' => __('Tabs')],
				['value' => 'accordion', 'label' => __('Accordion')]
			];
	}
}