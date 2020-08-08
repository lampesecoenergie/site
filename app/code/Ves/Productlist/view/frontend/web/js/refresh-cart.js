define([
	'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
	],
	function ($, customerData) {
		'use strict';
			return function (config, element) {
				jQuery(document).on("click", element, function(){
					alert("abc");
				});
			}
		}
	);