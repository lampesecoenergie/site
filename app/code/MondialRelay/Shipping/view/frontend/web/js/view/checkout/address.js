/*global define*/
define(
    ['ko'],
    function (ko) {
        'use strict';
        var pickupAddress = ko.observable();
        return {
            pickupAddress: pickupAddress
        };
    }
);