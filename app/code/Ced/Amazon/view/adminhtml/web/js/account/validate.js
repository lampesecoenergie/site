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

            var storeId = registry.get('index = store_id');
            var sellerId = registry.get('index = seller_id');
            var awsAuthId =  registry.get('index = aws_auth_id');
            var marketplace =  registry.get('index = marketplace');
            var name =  registry.get('index = name');
            var mode =  registry.get('index = mode');

            var cedcommerce =  registry.get('index = cedcommerce');
            var awsAccessKeyId =  registry.get('index = aws_access_key_id');
            var secretKey =  registry.get('index = secret_key');

            var message = 'Credentials are invalid.';
            if ((cedcommerce.value() == '0' && sellerId.hasData() && awsAccessKeyId.hasData() && secretKey.hasData() && marketplace.hasData()) ||
                (cedcommerce.value() == '1' && sellerId.hasData() && awsAuthId.hasData())
            ) {
                var data = {
                    store_id:  storeId.value(),
                    cedcommerce:  cedcommerce.value(),
                    seller_id:  sellerId.value(),
                    aws_access_key_id:  awsAccessKeyId.value(),
                    aws_auth_id:  awsAuthId.value(),
                    marketplace:  marketplace.value(),
                    name:  name.value(),
                    mode:  mode.value(),
                    secret_key:  secretKey.value()
                };

                var source = registry.get(this.provider);
                var error = true;
                var self = this;
                $.ajax({
                    method: "POST",
                    url: source.validate_url,
                    data: data,
                    complete: function (response) {
                        if (response.hasOwnProperty('responseJSON') &&
                            response['responseJSON'].hasOwnProperty('error')
                        ) {
                            error = response['responseJSON']['error'];
                            if (response['responseJSON'].hasOwnProperty('messages')) {
                                message = "";
                                $.each(response['responseJSON']['messages'], function (i,v) {
                                    message += " \n" + v;
                                })
                            }
                        }

                        if (error === false) {
                            message = 'Credentials are valid.';
                            self.showMessage(message);
                        } else {
                            self.showMessage(message, 'error');
                        }
                    }
                });
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
