/*global define*/
define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'MondialRelay_Shipping/js/view/shipping/pickup',
    'MondialRelay_Shipping/js/view/checkout/address',
    'MondialRelay_Shipping/js/model/shipping/pickup'
], function (Component, quote, pickupView, pickupAddress, pickupModel) {
    'use strict';

    return Component.extend({
        shippingMethod: quote.shippingMethod,

        initialize: function () {
            this._super();

            this.shippingMethod.subscribe(function (shippingMethod) {
                if (shippingMethod) {
                    var method = shippingMethod.carrier_code + '_' + shippingMethod.method_code;
                    var isPickup = method === window.checkoutConfig.mondialrelayPickup;

                    if (!isPickup) {
                        pickupView.prototype.pickupRemoveAddress(false);
                    } else {
                        var current = pickupModel.currentPickup(quote.getQuoteId());
                        current.complete(function (object) {
                            var pickup = object.responseJSON;
                            if (pickup.num) {
                                pickupAddress.pickupAddress(pickup);
                                pickupView.prototype.pickupUpdateAddress();
                            }

                            if (!pickupAddress.pickupAddress() && window.checkoutConfig.mondialrelayOpen === '1') {
                                pickupView.prototype.run();
                            }
                        });
                    }
                }
            });
        }
    });
});