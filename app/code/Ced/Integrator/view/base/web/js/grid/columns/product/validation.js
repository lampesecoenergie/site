define(
    [
        'Magento_Ui/js/grid/columns/column',
        'jquery',
        'Ced_Integrator/js/modal/popup'
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

                getShowHeading: function (row) {
                    return row[this.index + '_show_heading'];
                },

                getLabel: function (row) {
                    var html = '';
                    try {
                        if (row.hasOwnProperty(this.index + '_html')) {
                            html = row[this.index + '_html'];
                            var validation = row[this.index + '_productvalidation'];
                            if (validation !== "" && validation !== undefined) {
                                var data = $.parseJSON(validation);
                                if (typeof data === 'object') {
                                    var products = Object.keys(data).length;
                                    var counter = 0;
                                    var invalid = 0;
                                    $.each(
                                        data, function (index, value) {
                                            if (value.hasOwnProperty('errors') && value.errors === 'valid') {
                                                counter++;
                                            } else {
                                                invalid++;
                                            }
                                        }
                                    );

                                    if (counter === products) {
                                        html = '<table style="none;">' + "<tr>";
                                        if (this.getShowHeading(row)) {
                                            html += "<td class='cedcommerce errors validation' title='View Validation Errors'>V:</td>";
                                        }

                                        html += "<td class='cedcommerce errors'>" +
                                            "<div class='grid-severity-notice'><span>Valid</span></div></td>" +
                                            "</tr>";
                                    } else {
                                        html = '<table>' + "<tr>";
                                        if (this.getShowHeading(row)) {
                                            html += "<td class='cedcommerce errors validation' title='View Validation Errors'>V:</td>";
                                        }

                                        html += "<td class='cedcommerce errors'>" +
                                            "<div style='min-width: 90px' class='grid-severity-critical'>" +
                                            "<span>Invalid [" + invalid + "]</span>" +
                                            "</div></td>" +
                                            "</tr>";
                                    }
                                }
                            }

                            if (row.hasOwnProperty(this.index + '_product_feed_label')) {
                                html += row[this.index + '_product_feed_label'];
                            }

                            html += '</table>';
                        }
                    } catch (exception) {
                        console.log(exception);
                    }

                    return html;
                },

                getProductId: function (row) {
                    return row[this.index + '_productid'];
                },

                getProductValidation: function (row) {
                    var validation = false;
                    if (row.hasOwnProperty(this.index + '_productvalidation')) {
                        validation = row[this.index + '_productvalidation'];
                    }
                    return validation;
                },

                getProductFeed: function (row) {
                    var feed = false;
                    if (row.hasOwnProperty(this.index + '_product_feed_errors')) {
                        feed = row[this.index + '_product_feed_errors'];
                    }
                    return feed;
                },

                parseError: function (data) {
                    var self = this;
                    var messages = '';
                    var style = "";
                    if ((typeof data === 'object') && Object.keys(data).length > 0) {
                        messages += '<ul style="list-style: square;margin-left: 10px;">';
                        $.each(
                            data, function (id, value) {
                                if (id === 'required' || id === 'optional') {
                                    style = "text-transform: uppercase;";
                                }

                                if (typeof id === 'number') {
                                    if (typeof value === "string") {
                                        messages += '<li>' + value + '</li>';
                                    } else {
                                        messages += self.parseError(value);
                                    }
                                } else {
                                    messages += '<li><b style="' + style + '">' + id + '</b> : ' + self.parseError(value) + '</li>';
                                }
                            }
                        );
                        messages += '</ul>';
                    } else {
                        messages += data;
                    }

                    return messages;
                },

                startView: function (row) {
                    var self = this;
                    if (this.getProductValidation(row)) {
                        var data = $.parseJSON(this.getProductValidation(row));
                        var result = '<table class="data-grid" style="margin-bottom:25px"><tr><th class="data-grid-th">Id</th><th class="data-grid-th">SKU</th><th class="data-grid-th">Errors</th></tr>';
                        $.each(data, function (index, value) {
                                var messages = '';
                                if (value.hasOwnProperty('errors') && typeof value.errors === 'object') {
                                    messages = self.parseError(value.errors);
                                }

                                if (messages === '') {
                                    messages = '<b style="color:forestgreen;">No errors.</b>';
                                }

                                var sku = "<a href='" + value.url + "' target='_blank'>" + value.sku + "</a>";

                                sku += "<ul style='list-style: none;'>";
                                if (value.hasOwnProperty('account_id')) {
                                    sku += '<li>Account Id : ' + value.account_id + '</li>';
                                }

                                if (value.hasOwnProperty('store_id')) {
                                    sku += '<li>Store Id : &nbsp;&nbsp;&nbsp;&nbsp; ' + value.store_id + '</li>';
                                }

                                if (value.hasOwnProperty('profile_id')) {
                                    sku += '<li>Profile Id : &nbsp;&nbsp;&nbsp;' + value.profile_id + '</li>';
                                }
                                sku += "</ul>";

                                result += '<tr><td>' + (value.id) + '</td><td>' + sku + '</td><td>' + messages + '</td></tr>';
                            }
                        );
                        result += '</table>';

                        if (this.getProductFeed(row)) {
                            var feed = $.parseJSON(this.getProductFeed(row));
                            if (!this.empty(feed)) {
                                result += '<table class="data-grid"><tr><th class="data-grid-th">FeedId</th><th class="data-grid-th">AccountId</th><th class="data-grid-th">Marketplace</th><th class="data-grid-th">Result</th></tr>';
                                result += '<tr><td>' + feed['FeedId'] + '</td><td>' + feed['AccountId'] + '</td><td>' + feed['Marketplace'] + '</td><td>';
                                result += '<table class="data-grid" style="margin-bottom:25px"><tr><th class="data-grid-th">ResultCode</th><th class="data-grid-th">ResultMessageCode</th><th class="data-grid-th">ResultDescription</th></tr>';
                                $.each(feed['Result'], function (error, value) {
                                    result += '<tr><td>' + value['ResultCode'] + '</td><td>' + value['ResultMessageCode'] + '</td><td>' + value['ResultDescription'] + '</td></tr>';
                                });
                                result += '</table></td>';
                                result += '</tr></table>';
                            }
                        }

                        modal(
                            {
                                title: this.getTitle(row),
                                content: result
                            }
                        );
                    }
                },

                getFieldHandler: function (row) {
                    return this.startView.bind(this, row);
                },

                empty: function (e) {
                    switch (e) {
                        case "":
                        case 0:
                        case "0":
                        case null:
                        case false:
                            return true;
                        default:
                            if (typeof e === "undefined") {
                                return true;
                            } else if (typeof e === "object" && Object.keys(e).length === 0){
                                return true;
                            } else {
                                return false;
                            }
                    }
                }
            }
        );

    }
);
