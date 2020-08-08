define([
    'uiRegistry',
    'jquery',
    'Magento_Ui/js/form/element/select'
], function (uiRegistry, $, Select) {
    'use strict';
    return Select.extend({
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var queryBuilder = $("#amazon_profile_products");
           // TODO get via uiRegistry
            //queryBuilder.visible(false);
            if (this.empty(value)) {
                queryBuilder.hide();
            } else {
                queryBuilder.show();
            }
            return this._super();
        },

        empty: function (e) {
            switch (e) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                    return true;
                default:
                    if (typeof e === "undefined") {
                        return true;
                    } else if (typeof e === "object" && Object.keys(e).length === 0){
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});