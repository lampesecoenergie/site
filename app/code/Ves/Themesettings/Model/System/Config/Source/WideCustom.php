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
namespace Ves\Themesettings\Model\System\Config\Source;

class WideCustom implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
                /*['value' => 'default', 'label' => __('Default')],*/
                ['value' => '960px',  'label' => __('960 px')],
                ['value' => '1024px',  'label' => __('1024 px')],
                ['value' => '1280px', 'label' => __('1280 px')],
                ['value' => '1360px', 'label' => __('1360 px')],
                ['value' => '1440px', 'label' => __('1440 px')],
                ['value' => '1680px', 'label' => __('1680 px')],
                ['value' => 'custom', 'label' => __('Custom width...')]
            ];
    }
}