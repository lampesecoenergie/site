/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define([
 	'jquery',
 	'mage/translate',
 	'jquery/ui',
 	'Magento_Catalog/js/catalog-add-to-cart'
 	], function($, $t) {
 		"use strict";
 		$.widget('ves.catalogAddToCart', $.mage.catalogAddToCart,{
 			ajaxSubmit: function(form) {
 				var self = this;
 				$(self.options.minicartSelector).trigger('contentLoading');
 				self.disableAddToCartButton(form);
 				var url = form.attr('action');
 				$.ajax({
 					url: url,
 					data: form.serialize(),
 					type: 'post',
 					dataType: 'json',
 					beforeSend: function() {
 						if (self.isLoaderEnabled()) {
 							$('body').trigger(self.options.processStart);
 						}
 						$('body').append("<div id='fancybox-loading'><div></div></div>");
 						$('#ajax_loader').show();
 					},
 					success: function(res) {

 						jQuery('#ajax_loader').hide();
 						jQuery('body #fancybox-loading').remove();

 						if(res.html){
 							jQuery.fancybox({
 								content: res.html,
 								helpers: {
 									overlay: {
 										locked: false
 									}
 								}
 							});
 						}
 						$(self.options.minicartSelector).trigger('contentUpdated');

 						if (self.isLoaderEnabled()) {
 							$('body').trigger(self.options.processStop);
 						}

 						if (res.backUrl) {
 							window.location = res.backUrl;
 							return;
 						}
 						if (res.messages) {
 							$(self.options.messagesSelector).html(res.messages);
 						}
 						if (res.minicart) {
 							$(self.options.minicartSelector).replaceWith(res.minicart);
 							$(self.options.minicartSelector).trigger('contentUpdated');
 						}
 						if (res.product && res.product.statusText) {
 							$(self.options.productStatusSelector)
 							.removeClass('available')
 							.addClass('unavailable')
 							.find('span')
 							.html(res.product.statusText);
 						}
 						self.enableAddToCartButton(form);
 					}
 				});
			},
		})
	return $.ves.catalogAddToCart;
});