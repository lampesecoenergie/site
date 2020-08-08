/*global define*/
define([
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'MondialRelay_Shipping/js/view/shipping/pickup',
    'MondialRelay_Shipping/js/view/checkout/address'
], function (
    setShippingInformationAction,
    quote,
    stepNavigator,
    pickupView,
    address
) {
    'use strict';

    return function (target) {
        return target.extend({
            setShippingInformation: function () {
                var method = null;
                if (quote.shippingMethod()) {
                    method = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
                }

                if (method && method !== window.checkoutConfig.mondialrelayPickup) {
                    pickupView.prototype.pickupRemoveAddress(true);
                }

                if (method && method === window.checkoutConfig.mondialrelayPickup && !address.pickupAddress()) {
                    if (this.validateShippingInformation()) {
                        setShippingInformationAction().done(
                            function () {
                                pickupView.prototype.run();
                            }
                        );
                    }
                } else {
                    this._super();
                }
            }
        });
    }
});