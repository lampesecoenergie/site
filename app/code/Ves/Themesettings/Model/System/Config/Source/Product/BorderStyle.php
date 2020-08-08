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

class BorderStyle implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
				['value' => 'none', 'label' => __('None')],
				['value' => 'solid', 'label' => __('Solid')],
				['value' => 'dashed', 'label' => __('Dashed')],
				['value' => 'dotted', 'label' => __('Dotted')],
				['value' => 'double', 'label' => __('Double')],
				['value' => 'groove', 'label' => __('Groove')],
				['value' => 'inset', 'label' => __('Inset')],
				['value' => 'outset', 'label' => __('Outset')],
				['value' => 'ridge', 'label' => __('Ridge')],
			];
	}
}
