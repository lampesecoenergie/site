/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'ko',
        'MondialRelay_Shipping/js/view/checkout/address',
        'MondialRelay_Shipping/js/view/shipping/pickup',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        Component,
        ko,
        address,
        pickupView,
        quote
    ) {
        'use strict';
        return Component.extend({
            address: address.pickupAddress,
            totals: quote.getTotals(),
            defaults: {
                template: 'MondialRelay_Shipping/checkout/sidebar/pickup'
            },

            initialize: function () {
                this._super();
            },

            getPickupAddress: function () {
                return address.pickupAddress();
            },

            updatePickupAddress: function () {
                pickupView.prototype.run();
            },

            getShippingMethodTitle: function () {
                var shippingMethod;

                if (!this.isCalculated()) {
                    return '';
                }
                if (!this.address()) {
                    return '';
                }

                shippingMethod = quote.shippingMethod();

                return shippingMethod ? shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'] : '';
            },

            isCalculated: function () {
                return this.totals() && this.isFullMode() && quote.shippingMethod() !== null;
            }
        });
    }
);