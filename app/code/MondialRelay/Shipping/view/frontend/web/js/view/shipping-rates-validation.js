/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        mondialrelayShippingRatesValidator,
        mondialrelayShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('mondialrelay', mondialrelayShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('mondialrelay', mondialrelayShippingRatesValidationRules);
        return Component;
    }
);
