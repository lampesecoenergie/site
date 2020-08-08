define(
    [
        'jquery',
        'MondialRelay_Shipping/js/view/shipping/pickup',
        'MondialRelay_Shipping/js/view/checkout/address'
    ],
    function (
        $,
        pickupView,
        pickupAddress
    ) {
        'use strict';

        return {
            pickupInput: 'input[value=mondialrelay_pickup]:checked',

            /**
             * @returns {boolean}
             */
            validate: function () {
                var isValid = true;

                if ($(this.pickupInput).length) {
                    if (!pickupAddress.pickupAddress()) {
                        isValid = false;
                        pickupView.prototype.run();
                    }
                }

                return isValid;
            }
        }
    }
);