define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry',
    'jquery'
], function (Checkbox, registry, $) {
    'use strict';
    return Checkbox.extend({
        /**
         * @inheritdoc
         */
        onCheckedChanged: function (value) {
            this.show(value);
            return this._super(value);
        },

        show: function (value) {
            var awsAuthId = registry.get('index = aws_auth_id');
            if (awsAuthId) {
                awsAuthId.required(false);
            }

            var visible = true;
            if (value === true && awsAuthId) {
                visible = false;
                awsAuthId.required(true);
            }

            var accessKey = registry.get('index = aws_access_key_id');
            if (accessKey) {
                accessKey.visible(visible);
            }

            var secretKey = registry.get('index = secret_key');
            if (secretKey) {
                secretKey.visible(visible);
            }
        }
    });
});
