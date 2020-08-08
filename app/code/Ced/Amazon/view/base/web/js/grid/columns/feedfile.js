define([
    'Magento_Ui/js/grid/columns/actions'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        },
        downloadFile: function (row) {
            var url = row[this.index + '_url'];
            if (url) {
                window.open(url, '_blank');
            }
        },
        getFieldHandler: function (row) {
            return this.downloadFile.bind(this, row);
        }


    });

});
