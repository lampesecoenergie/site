/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
	map: {
		"*": {
			productlistowlcarousel: "Ves_Productlist/js/owl.carousel.min",
			productlistbootstrapcarousel: "Ves_Productlist/js/bootstrap.min",
			easytab: "Ves_Productlist/js/jquery.easytabs.min",
			countdown: "Ves_Productlist/js/countdown",
			productlist: "Ves_Productlist/js/productlist",
			productlistfancybox: 'Ves_Productlist/js/jquery.fancybox.pack',
			vesaddtocart: "Ves_Productlist/js/catalog-add-to-cart"
		}
	},
	shim: {
    	'productlistowlcarousel': {
            deps: ['jquery']
        },
        'productlistbootstrapcarousel': {
            deps: ['jquery']
        },
        'easytab': {
            deps: ['jquery']
        },
        'countdown': {
            deps: ['jquery']
        },
        'productlist': {
            deps: ['jquery']
        },
        'productlistfancybox': {
            deps: ['jquery']
        },
        'vesaddtocart': {
            deps: ['jquery']
        }
    }
};
