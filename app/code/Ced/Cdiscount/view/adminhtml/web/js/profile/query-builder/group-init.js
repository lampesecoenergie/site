define(['uiComponent','queryBuilderGroup'], function(Component, Group) {

    return Component.extend({
        initialize: function () {
            this._super();
            var group = new Group();
        }
    })
});