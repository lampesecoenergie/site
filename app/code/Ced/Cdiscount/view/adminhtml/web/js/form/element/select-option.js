define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
], function ($, _, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value)
        {
            if (value != 'undefined')
            {
                console.log('en');
                //Do your Ajx stuff here
            }
            return this._super();
        },
    });
});
