define([
    'uiRegistry',
    'Magento_Ui/js/form/form'
], function (uiRegistry, Form) {
    'use strict';
    return Form.extend({
        save: function (redirect, data) {
            this.validate();
            this.collectProducts();

            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            } else {
                this.focusInvalid();
            }
        },

        collectProducts: function () {
            var select = uiRegistry.get('amazon_profile_products.amazon_profile_products.product_columns.ids');
            if (!this.empty(select)) {
                var selections = select.getSelections();
                if (!this.empty(selections)) {
                    if (selections.total === 0) {
                        // If products are removed.
                        var filter = uiRegistry.get('index = filter');
                        filter.value(JSON.stringify({"selected": [], "namespace": "amazon_profile_products", "truncate": true}));
                    } else {
                        // If products are updated.
                        var actionAdd =
                            uiRegistry.get('amazon_profile_products.amazon_profile_products.listing_top.listing_massaction');
                        if (!this.empty(actionAdd)) {
                            actionAdd.applyAction('add');
                        }
                    }
                }
            }
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
                    } else if (typeof e === "object" && Object.keys(e).length === 0) {
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});