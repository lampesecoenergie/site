define([
    'underscore',
    'uiRegistry',
    'uiComponent'
], function (_, uiRegistry, uiComponent) {
    'use strict';

    return uiComponent.extend({
        /**
         * Initializes model instance.
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super()
                .initObservable()
                .initModules()
                .initStatefull()
                .initLinks()
                .initUnique();
            // this.addFilter();
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
        },
        addFilter: function () {
            // Adding profile id filter
            var profileId = uiRegistry.get('amazon_profile_form.amazon_profile_form.general_information.id');
            if (!this.empty(profileId) && !this.empty(profileId.value())) {
                var filter = uiRegistry.get('amazon_profile_products.amazon_profile_products.listing_top.listing_filters');
                // filter.applied.amazon_profile_id = profileId.value();
                // filter.filters.amazon_profile_id = profileId.value();
            }
        }
    });
});