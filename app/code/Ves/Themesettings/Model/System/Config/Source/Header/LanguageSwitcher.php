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
namespace Ves\Themesettings\Model\System\Config\Source\Header;

class LanguageSwitcher implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        $blocks = [];
        $blocks[] = [
	        'value' => 'Magento_Store::switch/languages.phtml',
	        'label' => 'Select Box'];
        $blocks[] = [
	        'value' => 'Magento_Store::switch/flags.phtml',
	        'label' => 'Flags'];
        return $blocks;
    }
}