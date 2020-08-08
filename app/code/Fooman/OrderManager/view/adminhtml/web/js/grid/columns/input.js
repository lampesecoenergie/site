define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/column'
], function (_, utils, registry, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Fooman_OrderManager/grid/cells/input'
        },

        formatTrackingNumber: function (value) {
            if (value.indexOf('|') > -1) {
                var items = value.split('|');
                var result = [];

                for (var i = 0; i < items.length; i++) {
                    result.push(this.formatTrackingNumber(items[i]));
                }

                return result.join(', ');
            }

            return value;
        },

        canShip: function (row) {
            return (!row.tracking_number || row.tracking_number.length === 0);
        },

        getFieldHandler: function (row) {
            return false;
        }

    });

});
