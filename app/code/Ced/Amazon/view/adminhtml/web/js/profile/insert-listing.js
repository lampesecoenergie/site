/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'underscore',
    'rjsResolver',
    'Magento_Ui/js/form/components/insert-listing'
], function (uiRegistry, _, resolver, InsertListing) {
    'use strict';

    return InsertListing.extend({
        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            _.bindAll(this, 'updateValue', 'updateExternalValueByEditableData');
            resolver(this.addProfileId, this);
            return this;
        },

        addProfileId: function () {
            var id = uiRegistry.get('index = id');
            this.params['profile_id'] = id.value();
        }
    });
});
