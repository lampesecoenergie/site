define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MondialRelay_Shipping/js/model/order-validator'
    ],
    function (
        Component,
        additionalValidators,
        orderValidator
    ) {
        'use strict';
        additionalValidators.registerValidator(orderValidator);
        return Component.extend({});
    }
);