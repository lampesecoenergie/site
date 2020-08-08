define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry',
    'jquery',
    'rjsResolver'
], function (Checkbox, registry, $, resolver) {
    'use strict';
    return Checkbox.extend({
        initialize: function () {
            this._super();

            resolver(this.initStore, this);

        },

        onUpdate: function (value) {
            this.show(value);

            return this._super();
        },

        initStore: function () {
            var flag = this.value();
            this.show(flag);
        },

        show: function (visible) {
            if (this.empty(visible)) {
                visible = false;
            } else {
                visible = true;
            }

            var defaultstore = registry.get('index = store_id');
            defaultstore.visible(!visible);

            var multistore = registry.get('index = multi_store_values');
            multistore.visible(visible);
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
