define(
    [
        'jquery',
        'mage/utils/wrapper',
        'Magento_Checkout/js/model/quote',
        'gls'
    ], function ($,
                 wrapper,
                 quote,
                 gls) {

        return function (setShippingInformationAction) {

            function isGlsRelay() {
                return quote.shippingMethod() !== null
                    && quote.shippingMethod().method_code.indexOf('relay_') != -1
                    && quote.shippingMethod().carrier_code == "gls";
            }

            return wrapper.wrap(setShippingInformationAction, function (originalAction) {
                if (isGlsRelay()) {
                    var shippingAddress = quote.shippingAddress();
                    if (shippingAddress) {
                        shippingAddress['company'] = gls.glsGetRelayName();
                        shippingAddress['city'] = gls.glsGetRelayCity();
                        shippingAddress['street'][0] = gls.glsGetRelayAddress();
                        shippingAddress['postcode'] = gls.glsGetRelayPostCode();
                    }
                }

                return originalAction();
            });
        }
    }
);