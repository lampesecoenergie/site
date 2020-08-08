

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

                getChildStatus: function (row) {
                    return row[this.index + '_child_product_status'];
                },

                startView: function (row) {
                    if (this.getFeedErrors(row)) {
                        var previewPopup = $('<div/>',{id : 'rueducommerce-feed-popup'+this.getProductId(row) });
                        var feedErrors = this.getFeedErrors(row);
                        var data = this.tryParseJSON(feedErrors);
                        if (data && Object.keys(data).length > 0) {
                            var result = this.CreateTableView(data);
                        }
                        var result = '<pre><div style="max-height: 300px; overflow: auto;">'+result+'</div></pre>';
                        var productStatus = this.getChildStatus(row);
                        var data = this.tryParseJSON(productStatus);
                        if (data && Object.keys(data).length > 0) {
                            var childresult = this.CreateTableView(data);
                        }
                        result += '<pre><div style="max-height: 300px; overflow: auto;">'+childresult+'</div></pre>';
                        //var result = "<pre>"+feedErrors+"</pre>";

                        var rueducommerceFeedPopup = previewPopup.modal(
                            {
                                title: this.getTitle(row),
                                innerScroll: true,
                                modalLeftMargin: 15,
                                buttons: [],
                                opened: function (row) {
                                    rueducommerceFeedPopup.append(result);
                                },
                                closed: function (row) { }
                            }
                        ).trigger('openModal');
                    }
                },

                getFieldHandler: function (row) {
                    return this.startView.bind(this, row);
                },
                CreateTableView: function (objArray, theme, enableHeader) {
                    // set optional theme parameter
                    if (theme === undefined) {
                        theme = {
                            'table': 'data-grid',
                            'td': '',
                            'th': 'data-grid-th',
                            'tr' :'data-row'
                        }; //default
                    }

                    if (enableHeader === undefined) {
                        enableHeader = true; //default enable headers
                    }

                    if (typeof objArray === 'function') {
                        return "";
                    }
                    if (typeof objArray === 'string') {
                        return objArray;
                    }

                    // If the returned data is an object do nothing, else try to parse
                    var array = typeof objArray != 'object' ? JSON.parse(objArray) : new Array(objArray);
                    var keys = Object.keys(array[0]);

                    var str = '<table class="' + theme.table + '">';

                    // table head
                    if (enableHeader) {
                        str += '<thead><tr class="'+theme.tr+'">';
                        for (var index in keys) {
                            str += '<th scope="col" class="'+theme.th+'">' + keys[index] + '</th>';
                        }
                        str += '</tr></thead>';
                    }

                    // table body
                    str += '<tbody>';
                    for (var i = 0; i < array.length; i++) {
                        str += (i % 2 == 0) ? '<tr class="alt" class="'+theme.tr+'">' : '<tr>';
                        for (var index in keys) {
                            var objValue = array[i][keys[index]];

                            // Support for Nested Tables
                            if (typeof objValue === 'object' && objValue !== null) {
                                if (Array.isArray(objValue)) {
                                    str += '<td class="'+theme.td+'">';
                                    for (var aindex in objValue) {
                                        str += this.CreateTableView(objValue[aindex], theme, true);
                                    }
                                    str += '</td>';
                                } else {
                                    str += '<td class="'+theme.td+'">' + this.CreateTableView(objValue, theme, true) + '</td>';
                                }
                            } else {
                                str += '<td class="'+theme.td+'">' + objValue + '</td>';
                            }
                        }
                        str += '</tr>';
                    }
                    str += '</tbody>';
                    str += '</table>';

                    return str;
                },
                tryParseJSON:function (jsonString){
                    try {
                        var o = JSON.parse(jsonString);

                        // Handle non-exception-throwing cases:
                        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
                        // but... JSON.parse(null) returns null, and typeof null === "object",
                        if (o && typeof o === "object") {
                            return o;
                        }
                    }
                    catch (e) { }

                    return false;
                }

            }
        );

    }
);
