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
            var html = row[this.index + '_html'];
            var validation = row[this.index + '_productvalidation'];
            if (validation) {
                var data = $.parseJSON(validation);
                if (data) {
                    var products = Object.keys(data).length;
                    var counter = 0;
                    $.each(data, function (index, value) {
                        if (Object.keys(value.errors).length === 0) {
                            counter++;
                        }
                    });
                    if (counter === products) {
                        html = "<div class='grid-severity-notice'><span>valid</span></div>";
                    }
                }
            }
            return html;
        },

        getProductId: function (row) {
            return row[this.index + '_productid'];
        },

        getProductvalidation: function (row) {
            return row[this.index + '_productvalidation'];
        },

        getFeedErrors: function (row) {
            return row[this.index + '_product_feed_errors'];
        },

        startView: function (row) {
            if (this.getProductvalidation(row)) {
                var previewPopup = $('<div/>',{id : 'rueducommercepopup'+this.getProductId(row) });
                var data = $.parseJSON(this.getProductvalidation(row));
                var result = '<table class="data-grid" style="margin-bottom:25px"><tr><th style="padding:15px">Sl. No.</th><th style="padding:15px">SKU</th><th style="padding:15px">Errors</th></tr>';
                $.each(data, function (index, value) {
                    var messages = '';
                    $.each(value.errors, function (i, v) {
                        if (typeof v === 'object' && Object.keys(v).length > 0) {
                            messages += '<ul style="list-style: none;">';
                            $.each(v, function (attribute, err) {
                                messages += '<li><b>'+attribute+'</b> : '+err+'</li>';
                            });
                            messages += '</ul>';
                        }
                    });

                    if (messages === '') {
                        messages = '<b style="color:forestgreen;">No errors.</b>';
                    }

                    var sku = "<a href='" + value.url + "' target='_blank'>" + value.sku + "</a>";
                    result += '<tr><td>' + (index) + '</td><td>'  + sku + '</td><td>' + messages + '</td></tr>';
                });
                result += '</table>';

                if (this.getFeedErrors(row)) {
                    var feedErrors = this.getFeedErrors(row);
                    result += "<pre>"+feedErrors+"</pre>";
                }

                var rueducommercepopup = previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalLeftMargin: 15,
                    buttons: [],
                    opened: function (row) {
                        rueducommercepopup.append(result);
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
