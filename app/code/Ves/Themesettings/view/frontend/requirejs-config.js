/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
	"map": {
		"*": {
			"vestheme": "Ves_Themesettings/js/theme",
			"countdown": "Ves_Themesettings/js/countdown",
			"themesettingsaddtocart": "Ves_Themesettings/js/catalog-add-to-cart",
			"jacklmoorezoom": "Ves_Themesettings/js/jacklmoorezoom",
		}
	},
	shim: {
        'Ves_Themesettings/js/jquery.fancybox.pack': {
            'deps': ['jquery']
        },
        'Magento_Catalog/js/jquery.zoom.min': {
        	'deps': ['jquery']
        }
    }
};