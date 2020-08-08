define([
    'Magento_Ui/js/grid/columns/column'
], function (Column, $, modal) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        }

    });

});
