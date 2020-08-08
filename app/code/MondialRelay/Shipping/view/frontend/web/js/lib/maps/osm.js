/*global define*/
define(
    [
        'jquery',
        'MondialRelay_Shipping/js/lib/maps/osm/leaflet'
    ],
    function ($, L) {
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

                var map = $('#' + mapId);
                if (map.length) {
                    this.map = L.map(mapId);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        id: 'mapbox.streets'
                    }).addTo(this.map);
                    this.map.attributionControl.setPrefix('');
                }
            },

            /**
             * Add locations to map
             *
             * @param {Object.<number, Object>} locations - All points on the map
             */
            locations: function (locations) {
                var osmMap = this;

                if (osmMap.map) {
                    var i;

                    var bounds = [];

                    for (i = 0; i < locations.length; i++) {
                        if (typeof locations[i] !== 'undefined') {
                            var icon = L.icon({iconUrl: locations[i][4], iconSize: [24, 25], popupAnchor: [0, -15]});
                            var latLng = [locations[i][1], locations[i][2]];
                            var marker = L.marker(latLng, {icon: icon, id:locations[i][3]}).addTo(osmMap.map);
                            marker.on('click', function (e) {
                                osmMap.select(e.sourceTarget.options.id)
                            });
                            marker.bindPopup(locations[i][0]);
                            osmMap.markers[locations[i][3]] = marker;

                            bounds.push(latLng);
                        }
                    }

                    if (bounds.length) {
                        osmMap.map.fitBounds(bounds);
                    }

                    if (bounds.length === 1) {
                        osmMap.map.setZoom(15);
                    }
                }
            },

            /**
             * Add specific address on map
             *
             * @param {string} address - Address to show on the map
             */
            address: function (address) {
                // Not implemented with OSM
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
                    this.markers[locationId].openPopup();
                }
            }
        };
    }
);