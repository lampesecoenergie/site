/*global define*/
define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    return function (target) {
        return target.extend({
            isVisible: function () {
                var isVisible = this._super();

                if (quote.shippingMethod()) {
                    var method = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;

                    if (method === window.checkoutConfig.mondialrelayPickup) {
                        isVisible = false;
                    }
                }

                return isVisible;
            }
        });
    }
});