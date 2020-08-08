define(['ko','uiComponent', 'queryBuilderCondition', 'queryBuilderGroupInit'], function(ko, Component, Condition, GroupInit) {

    return Component.extend({

        defaults: {
            template: 'Ced_Cdiscount/profile/query-builder/group-template'
        },

        constructor: function() {
            this.children = ko.observableArray();
            this.logicalOperators = ko.observableArray(['AND', 'OR']);
            this.selectedLogicalOperator = ko.observable('AND');

            // give the group a single default condition
            this.children.push(new Condition());

            this.addCondition = function () {
                this.children.push(new Condition());
            };

            this.initialize();
        },


        initialize: function () {
            this._super();
            this.group();
        },

        group: function () {
            this.children = ko.observableArray();
            this.logicalOperators = ko.observableArray(['AND', 'OR']);
            this.selectedLogicalOperator = ko.observable('AND');

            // give the group a single default condition
            this.children.push(new Condition());

            this.addCondition = function () {
                this.children.push(new Condition());
            };

            this.addGroup = function () {
                this.children.push(new GroupInit());
            };

            this.removeChild = function (child) {
                this.children.remove(child);
            };
            var self = this;
            // the text() function is just an example to show output
            this.text = ko.computed(function () {
                var result = '(';
                var op = '';
                for (var i = 0; i < self.children().length; i++) {
                    var child = self.children()[i];
                    result += op + child.text();
                    op = ' ' + self.selectedLogicalOperator() + ' ';
                }
                return result += ')';
            });
        }
    });
});