define(
    [
        'jquery',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'gls',
        'mage/translate'
    ], function ($,
                 addressList,
                 quote,
                 selectShippingMethodAction,
                 modal,
                 checkoutData,
                 setShippingInformationAction,
                 stepNavigator,
                 gls) {
        'use strict';

        var mixin = {
            selectShippingMethod: function (shippingMethod) {
                gls.glsPublicSetRelayId('');
                gls.glsAttachOnclickPopup($, quote, modal, checkoutData, addressList, shippingMethod);

                return this._super();
            },

            setShippingInformation: function () {
                if (this.validateShippingInformation() && this.glsCheckTelephone() && this.glsValidateChoiceRelay()) {
                    this._super();
                }
            },

            glsValidateChoiceRelay: function () {
                if (!gls.glsGetRelayId() && quote.shippingMethod().carrier_code == 'gls' && quote.shippingMethod().method_code.indexOf('relay_') != -1) {
                    this.errorValidationMessage($.mage.__('Please choose a relay for this shipping method'));
                    return false;
                } else {
                    gls.glsPublicSetRelayId('');
                    return true;
                }
            },

            glsCheckTelephone: function () {
                if (quote.shippingMethod().carrier_code == 'gls' && !quote.shippingAddress().telephone) {
                    this.errorValidationMessage($.mage.__('You have to set a phone number for the GLS shipping methods'));
                    return false;
                }
                return true;
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    });