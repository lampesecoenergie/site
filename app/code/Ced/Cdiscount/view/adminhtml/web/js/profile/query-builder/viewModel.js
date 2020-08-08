define(['ko', 'uiComponent', 'queryBuilderGroup'], function (ko, Component, Group) {

    return Component.extend({

        initialize: function () {
            this._super();
            this.viewModel();
        },

        viewModel: function () {
            this.group = ko.observable(new Group());
            var self = this;
            // the text() function is just an example to show output
            this.text = ko.computed(function () {
                return self.group().text();
            });
        }
    });
});
