define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'collapsable',
    'schema'
], function ($, ko, Component, _) {
    'use strict';


    // tab switcher
    // @todo move to better place
    if ($('#type').val() == 'xml') {
        $('#tabs_csv_section').hide();
    } else {
        $('#tabs_xml_section').hide();
    }

    $('#type').on('change', function () {
        if ($('#type').val() == 'xml') {
            $('#tabs_csv_section').hide();
            $('#tabs_xml_section').show();
        } else {
            $('#tabs_xml_section').hide();
            $('#tabs_csv_section').show();
        }
    });

    return Component.extend({
        defaults: {
            template: 'Mirasvit_Feed/template/edit/tab/schema/csv'
        },

        initialize: function () {
            var self = this;

            this._super();

            _.bindAll(this, 'removeRow');

            self.tmp = ko.observableArray([]);

            _.each(self.rows, function (row) {
                var obj = new $.Pattern().load(row);
                self.tmp.push(obj);
            });

            self.rows = self.tmp;
        },

        afterRender: function (element) {
            $('[data-role=row]', element).collapsable();

            $('[data-role=sortable], [data-role=sortable]', element).sortable({
                distance: 8,
                tolerance: 'pointer',
                axis: 'y',
                update: function () {
                    $('[data-role=order]', this).each(function (index, element) {
                        $(element).val(index + 1);
                    });
                }
            });
        },

        addRow: function () {
            var pattern = new $.Pattern().opened(true);
            this.rows.push(pattern);
        },

        removeRow: function (row) {
            this.rows.remove(row);
        },

        toggle: function (row, event) {
            row.opened(!row.opened());
        }
    });
});