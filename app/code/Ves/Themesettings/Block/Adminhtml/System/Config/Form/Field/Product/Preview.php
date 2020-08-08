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
namespace Ves\Themesettings\Block\Adminhtml\System\Config\Form\Field\Product;

/**
 * HTML select element block with customer groups options
 */
class Preview extends \Magento\Config\Block\System\Config\Form\Field
{
	/**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		$fieldIdSuffix = strstr($element->getHtmlId(), '_product_preview');
		$id = uniqid();
		$html = '';

		$html .= '<div class="ves-btn-preview">Close Product Preview</div>';

		$html .= '<div id="preview'.$id.'" class="ves-preview">';
		$html .= '<div class="ves-preview-close"><span>Close</span></div>';
		$html .= '<div class="item product product-item">';
		$html .= '<div class="product-block">';
		$html .= '<div class="product-item-info">';

		// Icon
		$html .= '<div class="top-icon">';
		$html .= '<span class="onsale"><span>Sale</span></span>';
		$html .= '<span class="new"><span>New</span></span>';
		$html .= '</div>'; // Icon

    	// Product Image
		$html .= '<div class="product-image">';
		$html .= '<i class="fa fa-picture-o"></i>';
		/*$html .= '<a href="#" title="main-image" class="main-image" ><img src=""/></a>';
		$html .= '<a href="#" title="hover-image" class="hover-image" ><img src=""/></a>';*/
		$html .= '</div>'; // Product Image

		$html .= '<div class="product details product-item-details">';

		// Product Name
		$html .= '<strong class="product name product-item-name"><span onclick="javscript:return false;" class="product-item-link product-name" href="#">Nanette Lepore Womens Rite Of Pass</span></strong>';

		// Quickview
		$html .= '<div class="quickview-wrapper">';
		$html .= '<div class="quickview btn-preview"><span>';
		$html .= '<i class="fa fa-eye"></i>';
		$html .= '<span class="vclass">Quickview</span></span>';
		$html .= '</div>';
		$html .= '</div>'; // Quickview

		// Product Price
		$html .= '<div class="price-box price-final_price" data-role="priceBox" data-product-id="1">';
		// Special Price
		$html .= '<span class="special-price-wrapper">';
		$html .= '<span class="price-container price-final_price tax weee">';
		$html .= '<span class="special-price-label price-label">Special Price</span>';
		$html .= '<span id="product-price-1" data-price-amount="270" data-price-type="finalPrice" class="price-wrapper "><span class="price special-price">$270.00</span></span>';
		$html .= '</span>';
		$html .= '</span>';
		// Old Price
		$html .= '<span class="old-price-wrapper">';
		$html .= '<span class="price-container ">';
		$html .= '<span class="old-price-label price-label">was</span>';
		$html .= '<span id="old-price-1" data-price-amount="290" data-price-type="oldPrice" class="price-wrapper "><span class="price old-price">$290.00</span></span>';
		$html .= '</span>';
		$html .= '</span>';
		$html .= '</div>';

		// Ratings
		$html .= '<div class="ratings">';
		$html .= '<div class="rating-box"><div class="rating" style="width:90%"></div></div>';
		/*$html .= '<p class="rating-links">
		<a href="#">5 Review(s)</a><span class="separator">|</span><a href="#">Add Your Review</a></p>';*/
		$html .= '</div>'; // Ratings

		//Count Down Timer
		$html .= '<div class="countdowntimer"><ul>
			<li><ul><li>3</li><li>0</li></ul></li>
			<li><ul><li>0</li><li>9</li></ul></li>
			<li><ul><li>1</li><li>4</li></ul></li>
			<li><ul><li>0</li><li>0</li></ul></li>
		</ul></div>';

		$html .= '<div class="actions">';
		// Add to cart
		//$html .= '<button class="addtocart" onclick="javscript:return false;" class="button"><i class="fa fa-shopping-cart"></i>Addto Cart</button>'; // Add to cart
		$html .= '<div class="addtocart btn-preview"><span>';
		$html .= '<i class="fa fa-shopping-cart"></i>';
		$html .= '<span class="vclass">Addto Cart</span></span>';
		$html .= '</div>';

		// Add to link
		$html .= '<div class="link-wishlist btn-preview"><span>';
		$html .= '<i class="fa fa-heart-o"></i>';
		$html .= '<span class="vclass">Add To Wishlist</span></span>';
		$html .= '</div>';
		$html .= '<div class="link-compare btn-preview"><span>';
		$html .= '<i class="fa fa-files-o"></i>';
		$html .= '<span class="vclass">Add To Compare</span></span>';
		$html .= '</div>';
		$html .= '</div>';

		// Short Description
		$html .= '<div class="desc std">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla</div>';

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$elements = [];
		$elements['product-item'] = [
			'bg_color' => 'background-color',
			'hover_bg_color' => 'background-color',
			'padding_top' => 'padding-top',
			'padding_right' => 'padding-right',
			'padding_bottom' => 'padding-bottom',
			'padding_left' => 'padding-left',
			'border_width' => 'border-width',
			'border_color' => 'border-color',
			'border_style' => 'border-style',
			];
		$elements['product-name'] = [
			'name_fonts' => 'font-family',
			'name_color' => 'color',
			'name_hover_color' => 'color'
			];
		$elements['product-image'] = [
			'image_bg_color' => 'background-color',
			'image_hover_bg_color' => 'background-color'
			];
		$elements['old-price'] = [
			'old_price_color' => 'color'
			];
		$elements['old-price-label'] = [
			'old_price_label_color' => 'color'
			];
		$elements['special-price'] = [
			'special_price_color' => 'color'
			];
		$elements['special-price-label'] = [
			'special_price_label_color' => 'color'
			];
		$elements['addtocart'] = [
			'addtocart_color' => 'color',
			'addtocart_hover_color' => 'color',
			'addtocart_bg_color' => 'background-color',
			'addtocart_hover_bg_color' => 'background-color',
			'addtocart_format_type' => true,
			'addtocart_format_text' => true,
			'addtocart_format_class' => true,
			];

		$elements['quickview'] = [
			'quickview_color' => 'color',
			'quickview_hover_color' => 'color',
			'quickview_bg_color' => 'background-color',
			'quickview_hover_bg_color' => 'background-color',
			'quickview_format_type' => true,
			'quickview_format_text' => true,
			'quickview_format_class' => true,
			];

		$elements['link-compare'] = [
			'compare_color' => 'color',
			'compare_hover_color' => 'color',
			'compare_bg_color' => 'background-color',
			'compare_hover_bg_color' => 'background-color',
			'compare_format_type' => true,
			'compare_format_text' => true,
			'compare_format_class' => true,
			];
		$elements['link-wishlist'] = [
			'whishlist_color' => 'color',
			'whishlist_hover_color' => 'color',
			'whishlist_bg_color' => 'background-color',
			'whishlist_hover_bg_color' => 'background-color',
			'wishlist_format_type' => true,
			'wishlist_format_text' => true,
			'wishlist_format_class' => true,
			];
		$elements['desc'] = [
			'short_description_color' => 'color'
			];
		$elements['countdowntimer'] = [
			'countdown_timer_color' => 'color'
			];
		$elements['new'] = [
			'new_label_color' => 'color',
			'new_label_bg_color' => 'background-color'
			];
		$elements['onsale'] = [
			'sale_label_color' => 'color',
			'sale_label_bg_color' => 'background-color'
			];
		$html .= '<script>
		require(["jquery"],function(){
			jQuery(function(){';
		$html .= 'function popShow(showLoad){
					var btnPreview = jQuery(".ves-btn-preview");
					var systemConfigTabs = jQuery("#system_config_tabs");
					var leftE = (systemConfigTabs.offset().left + systemConfigTabs.width()-jQuery(".ves-preview").width())+"px";
					jQuery(".ves-preview").css({"left":leftE});
					if(jQuery(btnPreview).hasClass("btn-close") || showLoad){
						jQuery(btnPreview).removeClass("btn-close").html("Close Product Preview");
						jQuery(".ves-preview").stop().animate({"top":"140px"});
					}else{
						jQuery(btnPreview).addClass("btn-close").html("Open Product Preview");
						jQuery(".ves-preview").stop().animate({"top":"-100%"});
					}
				}
				popShow(false);
				jQuery(".ves-btn-preview, .ves-preview-close").on("click",function(){
					popShow();
				});';
				foreach ($elements as $k => $_element) {
					if(!is_array($_element)) continue;
					foreach ($_element as $key => $val) {
						$id = uniqid();
						$htmlId = str_replace($fieldIdSuffix, '_'.$key, $element->getHtmlId());
						if(preg_match('/hover/', $key)){ // Hover
							$html .= '
							var originalData'.$id.' = "";
									jQuery(".'.$k.'").hover(function(){
										originalData'.$id.' = jQuery(this).css("'.$val.'")
											var val = jQuery("#'.$htmlId.'").val();
											if(val!=""){
												jQuery(".'.$k.'").css({"'.$val.'": val});
											}
									},function(){
										if(originalData'.$id.'!=""){
											jQuery(".'.$k.'").css({"'.$val.'": originalData'.$id.'});
										}
									});';
						}elseif(preg_match('/format_type/', $key)){ // Design Button, Link
							$html .= '
							jQuery("#'. $htmlId .'").on("change",function(){
								var element = ".'.$k.'";
								var val = jQuery(this).val();
								jQuery(element+" span:first").removeAttr("class").addClass(val);
							}).change();
							';
						}elseif(preg_match('/format_text/', $key)){ // Format Text
							$html .= '
							jQuery("#'. $htmlId .'").on("change",function(){
								var element = ".'.$k.'";
								var val = jQuery(this).val();
								jQuery(element+" .vclass").html(val);
							}).change();
							';
						}elseif(preg_match('/format_class/', $key)){ // Format Class
							$html .= '
							jQuery("#'. $htmlId .'").on("change",function(){
								var element = ".'.$k.'";
								var val = jQuery(this).val();
								jQuery(element+" i").removeAttr("class").addClass(val);
							}).change();
							';
						}elseif($val!=''){ // Color
							$html .= '
							jQuery("#'. $htmlId .'").on("change",function(){
								var element = ".'.$k.'";
								var val = jQuery(this).val();
								if(val!=""){
								jQuery(element).css({"'.$val.'": val});} }).change();
							';
						}
					}
				}
		$html .= '});
		});
		</script>';
		return $html;
	}
}