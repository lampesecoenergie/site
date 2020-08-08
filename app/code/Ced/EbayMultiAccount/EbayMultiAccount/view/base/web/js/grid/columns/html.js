define([
    'Magento_Ui/js/grid/columns/column',
    'jquery'
], function (Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        getTitle: function (row) {
            return row[this.index + '_title'];
        },

        getLabel: function (row) {
            console.log(row);
            return row[this.index + '_html'];
        },

        startView: function (row) {
            return true;
        },

        getFieldHandler: function (row) {
            return this.startView.bind(this, row);
        },
    });
});
