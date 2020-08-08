define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'jquery',
    'ko'
], function (Abstract, registry, $, ko) {
    'use strict';
    return Abstract.extend({
        validate: function () {
            self = this;
            self.validate.on = true;

            var password = registry.get('index = password');
            var cpass =  registry.get('index = confirm_password');
            var message = 'Password and Confirm Password must be same.';
            if (password.hasData() && cpass.hasData()) {
                if (password.value() !== cpass.value()) {
                    this.showMessage(message, 'error')
                } else {
                    this.error(false);
                    this.notice(false);
                }
            } else {
                this.showMessage(message, 'error')
            }
        },

        showLoader: function (status) {
            var body = $('body').loader();
            if (status === true) {
                body.loader('show');
            } else {
                body.loader('hide');
            }
        },

        showMessage: function (message, type) {
            if (type === 'error') {
                this.notice(false);
                this.error(message);
                this.bubble('error', message);
            } else {
                this.error(false);
                this.notice(message);
                this.bubble('notice', message);
            }
        }
    });
});
