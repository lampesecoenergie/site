define(
    [
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
    ], function (Column, $, modal) {
        'use strict';
        return Column.extend(
            {
                defaults: {
                    bodyTmpl: 'ui/grid/cells/html',
                },

                getTitle: function (row) {
                    return row[this.index + '_title'];
                },

                getLabel: function (row) {
                    return row[this.index + '_html'];
                },

                getProductId: function (row) {
                    return row[this.index + '_productid'];
                },



                getFeedErrors: function (row) {
                    return row[this.index + '_product_feed_errors'];
                },

                startView: function (row) {
                    if (this.getFeedErrors(row)) {
                        var previewPopup = $('<div/>',{id : 'lazada-feed-popup'+this.getProductId(row) });
                        //var data = $.parseJSON(this.getFeedErrors(row));
                        var feedErrors = this.getFeedErrors(row);
                        var result = "<pre>"+feedErrors+"</pre>";

                        var lazadaFeedPopup = previewPopup.modal(
                            {
                                title: this.getTitle(row),
                                innerScroll: true,
                                modalLeftMargin: 15,
                                buttons: [],
                                opened: function (row) {
                                    lazadaFeedPopup.append(result);
                                },
                                closed: function (row) { }
                            }
                        ).trigger('openModal');
                    }
                },

                getFieldHandler: function (row) {
                    return this.startView.bind(this, row);
                },

            }
        );

    }
);
