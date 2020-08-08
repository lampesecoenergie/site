define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/multiselect'
], function ($, _, uiRegistry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            preserveSelectionsOnFilter: true,
            preloaded: false
        },

        /**
         * Initializes column component.
         *
         * @returns {Column} Chainable.
         */
        initialize: function () {
            this._super()
                .initFieldClass();
            this.setSelections();
            return this;
        },

        // Remove this id useless
        removeSelectionIds: function () {
            var data = this.getSelections();
            var itemsType = data.excludeMode ? 'excluded' : 'selected';
            this[itemsType]([]);
        },

        getSelectionIds: function () {
            var data = this.getSelections();
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                ids = {};

            ids = data[itemsType];
            return ids;
        },

        setSelections: function () {
            if (!this.preloaded) {
                var filterValues = {
                    "selected": [],
                    "namespace": "amazon_profile_products"
                };
                var values = Object.create(null);

                // 'filter' is the field in the ui form stores all 'ids' in json filter format.
                var filter = uiRegistry.get('index = filter');
                if (!this.empty(filter)) {
                    if (!this.empty(filter.value())) {
                        filterValues = JSON.parse(filter.value());
                        Object.assign(values, filterValues)
                    }
                }

                for (var i = 0; i < Object.keys(values).length; i++) {
                    var key = Object.keys(values)[i];
                    if (this.hasOwnProperty(key)) {
                        var value = values[key];
                        this[key](value);
                    }
                }

                this.preloaded= true;
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
                    } else if (typeof e === "object" && Object.keys(e).length === 0){
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});