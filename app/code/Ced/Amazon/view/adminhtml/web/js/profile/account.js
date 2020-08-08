define([
    'jquery',
    'underscore',
    'uiRegistry',
    'rjsResolver',
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, resolver, Select, modal) {
    'use strict';

    return Select.extend({

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            this._super();

            $.async(
                this.rootListSelector,
                this,
                this.onRootListRender.bind(this)
            );

            resolver(this.initUpdateMarketplcace, this);

            return this;
        },

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);
            if (data.error) {
                return this;
            }

            this.cacheOptions.tree.push(option);
            this.cacheOptions.plain.push(option);
            this.options(this.cacheOptions.tree);
            this.setOption(option);
            this.set('newOption', option);

        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                is_active: '1',
                level: 0,
                value: data.account['id'],
                label: data.account['name'],
                parent: 0
            };
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.updateMarketplace(value);
            return this._super();
        },

        initUpdateMarketplcace: function () {
            var accountId = this.value();
            this.updateMarketplace(accountId);
        },

        updateMarketplace: function (accountId) {
            var marketplace = uiRegistry.get('index = marketplace');

            if (this.empty(accountId)) {
                marketplace.disable();
                this.disableCategory();
            } else {
                var profile = uiRegistry.get('index = id');
                if (!this.empty(profile.value())) {
                    marketplace.disable();
                    this.disable();
                } else {
                    marketplace.enable();
                }
                // TODO: REVIEW: Next change is skipped. As marketplaceId are already set by first select.
             //  if (this.empty(marketplace.value())) {
                    this.setMarketplace(accountId, marketplace);
            //   }
            }
        },

        /**
         * Disable category if marketplace is disabled.
         */
        disableCategory: function() {
            var category = uiRegistry.get('index = profile_sub_category');
            if (!this.empty(category)) {
                category.disable();
            }
        },

        /**
         * Set Marketplace ids available in account via ajax response.
         * @param accountId
         * @param marketplace
         */
        setMarketplace: function (accountId, marketplace) {
            var self = this;
            var parameters = {
                'id' : accountId
            };
            var meta = uiRegistry.get('index = meta');
            var url = meta.account_view_url;
            $.ajax({
                url: url,
                type: 'GET',
                data: parameters,
                dataType: 'json',
                showLoader: true
            }).done(function (response) {
                if (!self.empty(response) && response.hasOwnProperty('marketplace') && response.hasOwnProperty('marketplaceIds')) {
                    marketplace.setOptions(response['marketplace']);
                    marketplace.value(response['marketplaceIds']);
                }
            });
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