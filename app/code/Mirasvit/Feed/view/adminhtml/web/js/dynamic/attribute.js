define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Ui/js/lib/spinner',
    'collapsable'
], function ($, ko, _, Component) {
    'use strict';

    $.Condition = function () {
        var self = this;

        this.statements = ko.observableArray();

        this.resultType = ko.observable();
        this.resultValue = ko.observable();

        this.removeCondition = function (condition) {
            self.statements.remove(condition);
        };

        this.load = function (obj) {
            var self = this;
            _.each(obj.statement, function (row) {
                var itm = new $.Statement();
                itm.attribute(row.attribute);
                itm._operator = row.operator;
                itm._value = row.value;
                this.statements.push(itm);
            }, this);

            this.resultType(obj.result.type);
            this.resultValue(obj.result.value);
        }
    };

    $.Statement = function () {
        var self = this;

        this.attribute = ko.observable();
        this.operator = ko.observable();
        this._operator = false;
        this.attributeType = ko.observable();
        this.value = ko.observable();
        this._value = false;

        this.operators = ko.observableArray();
        this.values = ko.observableArray();

        this.attribute.subscribe(function () {
            $.ajax({
                method: 'GET',
                url: $.attributeUrl,
                showLoader: true,
                data: {
                    attribute: this.attribute()
                }
            }).done($.proxy(function (result) {
                this.operators(result.operators);
                this.attributeType(result.attributeType);
                this.values(result.values);

                if (this._operator) {
                    this.operator(this._operator);
                }
                if (this._value) {
                    this.value(this._value);
                }
            }, this));
        }, this);
    };


    return Component.extend({
        defaults: {
            template: 'Mirasvit_Feed/attribute_conditions'
        },

        initialize: function () {
            var self = this;

            this._super();

            _.bindAll(this, 'removeRow');

            this.rows = ko.observableArray([]);

            _.each(self.conditions, function (row) {
                var obj = new $.Condition();
                obj.load(row);

                self.rows.push(obj);
            });
        },

        afterRender: function (element) {
            $('[data-role=sortable]', element).sortable({
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
            var condition = new $.Condition();
            this.rows.push(condition);
        },

        removeRow: function (row) {
            this.rows.remove(row);
        },

        addCondition: function (model) {
            var sub = new $.Statement();
            model.statements.push(sub);
        }
    });

});
