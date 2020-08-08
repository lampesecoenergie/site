define([
    'uiElement',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Ced_Amazon/system/dashboard'
        },

        initialize: function () {
            this._super();
            this.data = ko.observable([]);
            this.sync();
        },

        getData: function () {
            return this.data();
        },

        accounts: function () {
            return this.getData().hasOwnProperty('accounts') ? this.getData()['accounts'] : [];
        },

        system: function () {
            return this.getData().hasOwnProperty('system') ? this.getData()['system'] : [];
        },

        present: function (key) {
            return !!(key && this.getData().hasOwnProperty(key) && !this.empty(this.getData()[key]));
        },

        sync: function (refresh) {
            if (refresh === undefined) {
                refresh = 0;
            }
            var self = this;
            $.ajax({
                showLoader: true,
                type: "GET",
                url: this.url,
                data: {"form_key": window.FORM_KEY, "refresh": refresh}
            }).done(function (response) {
                self.data(response);
            });
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
    });
});