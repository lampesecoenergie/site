/*global define*/
define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    return function (target) {
        return target.extend({
            canUseShippingAddress: function () {
                var canUseShippingAddress = this._super();

                var method = null;
                if (quote.shippingMethod()) {
                    method = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
                }

                if (method && method === window.checkoutConfig.mondialrelayPickup) {
                    canUseShippingAddress = false;
                }

                return canUseShippingAddress;
            }
        });
    }
});