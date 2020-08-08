define([
    'underscore',
    'rjsResolver',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/grid/provider'
], function (_, resolver, uiRegistry, utils, Provider) {
    'use strict';

    return Provider.extend({
        defaults: {
            storageConfig: {
                component: 'Ced_Amazon/js/profile/product/data-storage',
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                updateUrl: '${ $.update_url }'
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'onParamsChange', 5);
            _.bindAll(this, 'onReload');

            this._super()
                .initStorage()
                .clearData();

            resolver(this.addStoreId, this);

            // Load data when there will
            // be no more pending assets.
            resolver(this.reload, this);

            return this;
        },

        addStoreId: function () {
            var storeId = uiRegistry.get('index = store_id');
            this.params.filters.store_id = storeId.value();
            //TODO: REVIEW: if of any use
        }
    });
});