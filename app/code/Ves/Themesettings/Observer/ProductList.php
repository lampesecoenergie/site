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
namespace Ves\Themesettings\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductList implements ObserverInterface
{
	/**
	 * @var \Ves\Themesettings\Helper\Theme
	 */
	protected $_ves;

	public function __construct(\Ves\Themesettings\Helper\Theme $ves)
	{
		$this->_ves = $ves;
	}

	/**
     * Add coupon's rule name to order data
     *
     * @param EventObserver $observer
     * @return $this
     */
	public function execute(EventObserver $observer)
	{
		$ves = $this->_ves;
		$itemSettings = [
			'show_name',
			'show_name_single_line',
			'show_short_description',
			'short_max_char',
			'show_learnmore',
			'show_price',
			'show_review',
			'show_countdowntimer',
			'show_quickview',
			'quickview_popup_height',
			'quickview_popup_width',
			'show_addtocart',
			'addtocart_popup_height',
			'addtocart_popup_width',
			'show_wishlist',
			'show_compare',
			'show_new_label',
			'show_sale_label',
			'show_image',
			'aspect_ratio',
			'image_width',
			'image_height',
			'alt_image',
			'alt_image_column',
			'alt_image_column_value'
		];
		if($ves->getProductListingCfg('general/ovveride_productlist')){
			$obj = $observer->getEvent()->getProductList();
			foreach ($itemSettings as $k => $v) {
				$setting = $ves->getProductListingCfg('product_settings/'.$v);
				//echo $v.':'.var_dump($setting).'____';
				$obj->setData($v, $setting);
			}
		}

		$itemDesign = [
			'quickview_format_type',
			'quickview_format_text',
			'quickview_format_class',
			'addtocart_format_type',
			'addtocart_format_text',
			'addtocart_format_class',
			'wishlist_format_type',
			'wishlist_format_text',
			'wishlist_format_class',
			'compare_format_type',
			'compare_format_text',
			'compare_format_class'
		];
		if($ves->getProductListingCfg('general/ovveride_productlist')){
			$obj = $observer->getEvent()->getProductList();
			foreach ($itemDesign as $k => $v) {
				$setting = $ves->getProductListingCfg('design/'.$v);
				//echo $v.':'.var_dump($setting).'____';
				$obj->setData($v, $setting);
			}
		}
	}
}