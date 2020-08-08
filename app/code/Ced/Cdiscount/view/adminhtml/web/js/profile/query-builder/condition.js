define(['ko','uiComponent'], function(ko, Component) {

    return Component.extend({

        defaults: {
            template: 'Ced_Cdiscount/profile/query-builder/condition-template'
        },

        initialize: function () {
            this._super();
            this.condition();
        },

        condition: function () {

            this.fields = ko.observableArray(['Points', 'Goals', 'Assists', 'Shots', 'Shot%', 'PPG', 'SHG', 'Penalty Mins']);

            this.selectedField = ko.observable('Points');

            this.comparisons = ko.observableArray(['=', '<>', '<', '<=', '>', '>=']);

            this.selectedComparison = ko.observable('=');

            this.value = ko.observable(0);
            var self = this;
            // the text() function is just an example to show output
            this.text = ko.computed(
                function () {
                return self.selectedField() +
                    ' ' +
                    self.selectedComparison() +
                    ' ' +
                    self.value();
            });
        }
    });
});