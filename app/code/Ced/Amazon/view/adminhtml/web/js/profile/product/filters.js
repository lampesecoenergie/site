define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/filters/filters'
], function (_, uiRegistry, Filters) {
    'use strict';

    return Filters.extend({
        /**
         * Initializes filters component.
         *
         * @returns {Filters} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'updateActive');

            this._super()
                .initChips()
                .cancel();

            return this;
        },

        /**
         * Clears filters data.
         *
         * @param {Object} [filter] - If provided, then only specified
         *      filter will be cleared. Otherwise, clears all data.
         * @returns {Filters} Chainable.
         */
        clear: function (filter) {
            filter ?
                filter.clear() :
                _.invoke(this.active, 'clear');

            this.apply();

            /*if (filter.index === "amazon_profile_id") {
                var select = uiRegistry.get('amazon_profile_products.amazon_profile_products.product_columns.ids');
                if (!this.empty(select)) {
                    var ids = select.getSelectionIds();
                    if (this.empty(ids)) {
                        console.log(ids)
                        // Setting the initial set values.
                        select.setSelections();
                    }
                }
            }*/

            return this;
        },

        /**
         * Finds filters whith a not empty data
         * and sets them to the 'active' filters array.
         *
         * @returns {Filters} Chainable.
         */
        updateActive: function () {
            // console.log(this);
            var self = this;
            // Adding profile id filter
            // var profileId = uiRegistry.get('amazon_profile_form.amazon_profile_form.general_information.id');
            // if (!this.empty(profileId) && !this.empty(profileId.value())) {
            //     this.applied.amazon_profile_id = profileId.value();
            //     this.filters.amazon_profile_id = profileId.value();
            // }


            var applied = _.keys(this.applied);

            this.active = this.elems.filter(function (elem) {
                // if (elem.index === "amazon_profile_id" &&
                //     !self.empty(profileId) &&
                //     !self.empty(profileId.value())) {
                //     elem.value(profileId.value());
                // }
                return _.contains(applied, elem.index);
            });

            return this;
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