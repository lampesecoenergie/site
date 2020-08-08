define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/column'
], function (_, utils, registry, Column) {
    'use strict';

    return Column.extend({
        carriers: window.carrierConfig.carriers,

        defaults: {
            bodyTmpl: 'Fooman_OrderManager/grid/cells/select',
            preselectedCarrier: window.carrierConfig.preselectedCarrier
        },

        formatCarrierName: function (value) {
            if (value.indexOf('|') > -1) {
                var items = value.split('|');
                var result = [];

                for (var i = 0; i < items.length; i++) {
                    result.push(this.formatCarrierName(items[i]));
                }

                return result.join(', ');
            }

            for (var i = 0; i < this.carriers.length; i++) {
                if (this.carriers[i].value === value) {
                    return this.carriers[i].text;
                }
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
