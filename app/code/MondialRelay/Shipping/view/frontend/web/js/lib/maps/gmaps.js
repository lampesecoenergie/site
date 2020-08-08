/*global define*/
define([
    'jquery'
], function (
    $
) {
    'use strict';
    return {
        map: null,
        listId: null,
        infowindow: null,
        markers: [],

        /**
         * Load map
         *
         * @param {string} mapId - Id of Map element
         * @param {string} listId - Id of location list
         */
        run: function (mapId, listId) {
            this.listId = listId;
            this.infowindow = new google.maps.InfoWindow();

            var map = $('#' + mapId);
            if (map.length) {
                this.map = new google.maps.Map(document.getElementById(mapId), {
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
            }
        },

        /**
         * Add locations to map
         *
         * @param {Object.<number, Object>} locations - All points on the map
         */
        locations: function (locations) {
            if (this.map) {
                var googleMap = this;
                var bounds = new google.maps.LatLngBounds();
                var marker, i;

                for (i = 0; i < locations.length; i++) {
                    if (typeof locations[i] !== 'undefined') {
                        var LatLng = new google.maps.LatLng(locations[i][1], locations[i][2]);
                        bounds.extend(LatLng);
                        var markerInfo = {position: LatLng, map: googleMap.map};
                        if (locations[i][4]) {
                            markerInfo.icon = locations[i][4];
                        }
                        marker = new google.maps.Marker(markerInfo);
                        googleMap.markers[locations[i][3]] = {content: locations[i][0], marker: marker};
                        google.maps.event.addListener(marker, 'click', (function (marker, i) {
                            return function () {
                                googleMap.infowindow.setContent(locations[i][0]);
                                googleMap.infowindow.open(googleMap.map, marker);
                                googleMap.select(locations[i][3]);
                            }
                        })(marker, i));
                    }
                }

                googleMap.map.fitBounds(bounds);

                if (locations.length === 1) {
                    googleMap.map.setZoom(15);
                }
            }
        },

        /**
         * Add specific address on map
         *
         * @param {string} address - Address to show on the map
         */
        address: function (address) {
            if (this.map) {
                var googleMap = this;
                var geocoder = new google.maps.Geocoder();

                geocoder.geocode({address: address}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        new google.maps.Marker({
                            map: googleMap.map,
                            position: results[0].geometry.location
                        });
                    }
                });
            }
        },

        /**
         * Select location in the list
         *
         * @param {string} inputId - Id of input element
         */
        select: function (inputId) {
            var input = $('#' + inputId);
            if (input) {
                var list = $('#' + this.listId);

                $('#' + this.listId + ' li').each(function () {
                    $(this).removeClass('active');
                });
                input.prop('checked', true);
                input.parent('li').addClass('active');

                list.scrollTop(list.scrollTop() - 16 - list.offset().top + input.offset().top);
            }
        },

        /**
         * Show marker on map
         *
         * @param {string} locationId - Id of the marker
         */
        update: function (locationId) {
            if (this.markers[locationId] && this.map) {
                this.infowindow.setContent(this.markers[locationId].content);
                this.infowindow.open(this.map, this.markers[locationId].marker);
            }
        }
    };
});