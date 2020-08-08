define([
    'Magento_Ui/js/grid/columns/actions',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Column, $, modal) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            marketplace: {
                "US": "ATVPDKIKX0DER",
                "CA": "A2EUQ1WTGCTBG2",
                "MX": "A1AM78C64UM0Y8",
                "ES": "A1RKKUPIHCS9HS",
                "UK": "A1F83G8C2ARO7P",
                "FR": "A13V1IB3VIYZZH",
                "DE": "A1PA6795UKMFR9",
                "IT": "APJ6JRA9NG5V4",
                "BR": "A2Q3Y263D00KWC",
                "IN": "A21TJRUUN4KGV",
                "CN": "AAHKV2X7AFYLW",
                "JP": "A1VC38T7YXB528",
                "AU": "A39IBJ37TRP1C6"
            }
        },

        getLabel: function (row) {
            var status = this.tryParseJSON(row[this.index]);
            var html = '<table><tbody>';
            var haveValue = false;

            $.each(this.marketplace, function (i, v) {
                if (status && status.hasOwnProperty(v)) {
                    haveValue = true;
                    v = status[v];
                    var className = 'grid-severity-notice';
                    if (v === "Inactive") {
                        className = "grid-severity-minor"
                    }
                    html += '<tr><td class="cedcommerce errors feed">' + i + '</td>' +
                        '<td class="cedcommerce errors"><div class="'+className+'">' +
                        v + '</div></td></tr>';
                }
            });

            if (!haveValue) {
                html += '<tr><td class="cedcommerce errors feed">NA</td>' +
                    '<td class="cedcommerce errors"><div class="grid-severity-minor">' +
                    'NA</div></td></tr>';
            }

            html += '</tbody></table>';

            return html;
        },

        getFieldHandler: function (row) {
        },

        tryParseJSON: function (jsonString) {
            try {
                var o = JSON.parse(jsonString);

                // Handle non-exception-throwing cases:
                // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
                // but... JSON.parse(null) returns null, and typeof null === "object",
                if (o && typeof o === "object") {
                    return o;
                }
            } catch (e) {
            }

            return false;
        },
    });

});
