define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Column, $, modal) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
        },

        getTitle: function (row) {
            return row[this.index + '_title'];
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        },

        getFeedId: function (row) {
            return row[this.index + '_feedid'];
        },

        getFeedErrors: function (row) {
            return row[this.index + '_feederrors'];
        },
        startView: function (row) {
            if (this.getFeedErrors(row)) {
                //console.log(this.getFeedId(row));
                var previewPopup = $('<div/>',{id : 'ebaymultiaccountpopup'+this.getFeedId(row) });
                var data = $.parseJSON(this.getFeedErrors(row));
                var result = '<table class="data-grid" style="margin-bottom:25px; margin-top:25px"><tr><th style="padding:15px">Sl. No.</th><th style="padding:15px">SKU</th><th style="padding:15px">Status</th><th style="padding:15px">Errors</th></tr>';

                $.each(data.itemIngestionStatus, function(index, value){
                    var errors = "";
                    var slno = (index + 1);
                    $.each(value.ingestionErrors.ingestionError, function(i, error) {
                        // console.log(error);
                        errors += error.description;
                    });
                    result += '<tr><td>' + slno + '</td><td>' + value.sku + '</td><td>' + value.ingestionStatus + '</td><td>' + errors + '</td></tr>';
                });
                result += '</table>';
                var ebaymultiaccountpopup = previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalLeftMargin: 15,
                    buttons: [],
                    opened: function (row) {
                        ebaymultiaccountpopup.append(result);
                    },
                    closed: function (row) { }
                }).trigger('openModal');
            }
        },

        getFieldHandler: function (row) {
            return this.startView.bind(this, row);
        },

    });

});
