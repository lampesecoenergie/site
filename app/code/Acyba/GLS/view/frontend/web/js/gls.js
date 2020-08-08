define([
    'jquery',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, checkoutData, quote, confirmation) {

    var glsGoogleMap;
    var glsOpenedInfoWindow;
    var glsMarkersArray = [];
    var glsAjaxSetRelayInformationUrl;
    var glsRelayId;
    var glsRelayName;
    var glsRelayAddress;
    var glsRelayCity;
    var glsRelayPostCode;
    var popup;

    var glsShowRelaysMap = function () {

        glsClearMarkers();

        if ($(".gls_relay").length !== 0) {
            var bounds = new google.maps.LatLngBounds();

            $(".gls_relay").each(function (index, element) {
                var relayPosition = new google.maps.LatLng($(this).find('.gls_relay_latitude').text(), $(this).find('.gls_relay_longitude').text());

                var markerGls = new google.maps.Marker({
                        map: glsGoogleMap,
                        position: relayPosition,
                        title: $(this).find('.gls_relay_name').text(),
                        icon: $(this).find('.gls_path_marker').text()
                    }
                );

                var infowindowGLS = glsInfoWindowGenerator($(this));

                glsAttachClickInfoWindow(markerGls, infowindowGLS, index);

                glsAttachClickChooseRelay(element);

                glsMarkersArray.push(markerGls);
                bounds.extend(relayPosition);
            });
            glsGoogleMap.fitBounds(bounds);
        }
    };


    var glsClearMarkers = function () {
        glsMarkersArray.forEach(function (element) {
            element.setMap(null);
        });

        glsMarkersArray.length = 0;
    };


    var glsInfoWindowGenerator = function (relay) {

        var indexRelay = relay.find('.gls_relay_index').text();

        contentString = '<div class="info_window_gls">';

        contentString += '<span class="store_name">' + relay.find('.gls_relay_name').text() + '</span>';

        contentString += '<span class="store_address">' + relay.find('.gls_relay_address').text() + '<br>' +
            relay.find('.gls_relay_zipcode').text() + ' ' + relay.find('.gls_relay_city').text() + '</span>';

        contentString += '<span class="store_schedule">' + relay.find('.gls_relay_schedule').html() + '</span>';

        contentString += '<div class="gls_relay_choose gls_relay_popup_choose" data-relayindex=' + indexRelay + '>' + $.mage.__("Choose this relay") + '</div>';

        contentString += '</div>';

        infowindow = new google.maps.InfoWindow({
            content: contentString
        });

        return infowindow;
    };


    var glsAttachClickInfoWindow = function (marker, infoWindow, index) {

        marker.addListener('click', function () {
            glsClickHandler(marker, infoWindow);
        });

        $("#gls_relay_" + index).click(function () {
            glsClickHandler(marker, infoWindow);
        });
    };

    var glsAttachClickChooseRelay = function (element) {
        var divChooseRelay = $(element).find(".gls_relay_choose");
        var relayIndex = divChooseRelay.attr("data-relayindex");

        $(divChooseRelay).click(function () {
            glsAttachOnclickConfirmationRelay(relayIndex);
        })
    };


    var glsClickHandler = function (marker, infoWindow) {

        if (glsOpenedInfoWindow) {
            glsOpenedInfoWindow.close();
        }

        infoWindow.open(glsGoogleMap, marker);
        glsOpenedInfoWindow = infoWindow;

        var glsInfoWindowContent = $('.info_window_gls');

        if (glsInfoWindowContent.length == 1) {
            glsInfoWindowContent.each(function (index, element) {
                glsAttachClickChooseRelay(element);
            });
        } else console.error($.mage.__("Error: more than 1 info windows are open"));
    };


    var glsChooseRelay = function (relayIndex) {
        var relayClicked = $("#gls_relay_" + relayIndex);

        if (relayClicked !== null) {
            var nameChosenRelay = relayClicked.find('.gls_relay_name').text();
            var addressChosenRelay = relayClicked.find('.gls_relay_address').text();
            var zipcodeChosenRelay = relayClicked.find('.gls_relay_zipcode').text();
            var cityChosenRelay = relayClicked.find('.gls_relay_city').text();
            var idChosenRelay = relayClicked.find('.gls_relay_id').text();

            glsSetSessionRelayInformation(idChosenRelay, nameChosenRelay, addressChosenRelay, zipcodeChosenRelay, cityChosenRelay);

            glsPrivateSetRelayId(idChosenRelay);

            glsSetRelayCity(cityChosenRelay);
            glsSetRelayPostCode(zipcodeChosenRelay);
            glsSetRelayAddress(addressChosenRelay);
            glsSetRelayName(nameChosenRelay);

            glsAppendChosenRelay(nameChosenRelay, addressChosenRelay, zipcodeChosenRelay, cityChosenRelay);

            $('#layer_gls_wrapper').modal("closeModal");
        } else console.error($.mage.__('Error : can\'t select relay'));

    };

    var glsSetSessionRelayInformation = function (relayId, relayName, relayAddress, relayPostCode, relayCity) {
        if (relayId.length != 0) {
            $.ajax({
                url: glsAjaxSetRelayInformationUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    relayId: relayId,
                    relayName: relayName,
                    relayAddress: relayAddress,
                    relayPostCode: relayPostCode,
                    relayCity: relayCity
                },
                complete: function (response) {
                }
            });
        }
    };


    var glsAttachOnclickConfirmationRelay = function (relayIndex) {

        var relayClicked = $("#gls_relay_" + relayIndex);

        if (relayClicked !== null) {
            var nameChosenRelay = relayClicked.find('.gls_relay_name').text();
            var addressChosenRelay = relayClicked.find('.gls_relay_address').text();
            var zipcodeChosenRelay = relayClicked.find('.gls_relay_zipcode').text();
            var cityChosenRelay = relayClicked.find('.gls_relay_city').text();

            confirmation({
                title: $.mage.__('Confirm relay'),
                content: $.mage.__('Do you confirm the shipment to this relay:') + '<br>'
                + nameChosenRelay + '<br>'
                + addressChosenRelay + '<br>'
                + zipcodeChosenRelay + ' ' + cityChosenRelay,
                actions: {
                    confirm: function () {
                        glsChooseRelay(relayIndex);
                    },
                    cancel: function () {
                    },
                    always: function () {
                    }
                }
            });
        } else console.error($.mage.__('Error : can\'t select relay'));
    };


    var glsAppendChosenRelay = function (nameRelay, addressRelay, zipcodeRelay, cityRelay) {
        var chosenRelay = '<p>'
            + nameRelay
            + '</p><p>'
            + addressRelay
            + '</p><p>'
            + zipcodeRelay
            + ' '
            + cityRelay
            + '</p>'
            + '<p id="gls_change_my_relay">' + $.mage.__('Modify my relay') + '</p>';


        if ($('#gls_chosen_relay').length) {
            $('#gls_chosen_relay').html(chosenRelay);
        } else {
            $("<div>").attr('id', 'gls_chosen_relay').appendTo('#label_method_relay_fr_gls');
            $('#gls_chosen_relay').html(chosenRelay);
        }
    };


    /**
     * Trigger pour dire que la div qui contient la GMap a été redimensionné et qu'il faut donc recharger la GMap
     */
    var glsMapResize = function () {
        google.maps.event.trigger(glsGoogleMap, "resize");
    };

    var glsPrivateSetRelayId = function (relayId) {
        glsRelayId = relayId;
    };

    var glsSetRelayName = function (relayName) {
        glsRelayName = relayName;
    };

    var glsSetRelayCity = function (relayCity) {
        glsRelayCity = relayCity;
    };

    var glsSetRelayPostCode = function (relayPostCode) {
        glsRelayPostCode = relayPostCode;
    };

    var glsSetRelayAddress = function (relayAddress) {
        glsRelayAddress = relayAddress;
    };


    return {
        glsLoadMap: function () {
            var mapOptions = {
                zoom: 10,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: {lat: 48.866667, lng: 2.333333},
                disableDefaultUI: true
            };
            glsGoogleMap = new google.maps.Map(document.getElementById("gls_map"), mapOptions);
        },

        glsLoadRelayList: function (ajaxLoadRelaysUrl) {
            var zipCode = $('#cp_address_search').val();
            var address = $('#address_address_search').val();
            var city = $('#city_address_search').val();

            $.ajax({
                url: ajaxLoadRelaysUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    zipCode: zipCode,
                    address: address,
                    city: city
                },
                beforeSend: function () {
                    $("#gls_loader_relay").show();
                },
                success: function (response) {
                    var relaysHtml = response.html;
                    if (relaysHtml.length == 0) {
                        $("#gls_right").html($.mage.__('No relays found'));
                    } else $("#gls_right").html(relaysHtml);
                    glsShowRelaysMap();
                    glsMapResize();
                },
                complete: function () {
                    $("#gls_loader_relay").hide();
                }
            });
        },

        glsAttachOnclickPopup: function ($, quote, modal, checkoutData, addressList, shippingMethod) {
            var postCode, street, city;

            var shippingAddress;

            if (addressList().length > 0) {
                shippingAddress = quote.shippingAddress()
            } else if (checkoutData.getShippingAddressFromData()) {
                shippingAddress = checkoutData.getShippingAddressFromData();
            }


            if (shippingAddress != null) {
                postCode = shippingAddress.postcode;
                street = shippingAddress.street['0'];
                city = shippingAddress.city;

                if (postCode !== undefined) $('#cp_address_search').val(postCode);
                if (street !== undefined) $('#address_address_search').val(street);
                if (city !== undefined) $('#city_address_search').val(city);
            }

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: []
            };

            var divPopupGls = $('#layer_gls_wrapper');

            if (popup === undefined) {
                popup = modal(options, divPopupGls);
            }

            if (shippingMethod !== null
                && shippingMethod.method_code.indexOf('relay_') != -1
                && shippingMethod.carrier_code == "gls"
                && !popup.options.isOpen
            ) {
                divPopupGls.modal("openModal");
                if (postCode !== undefined) {
                    $("#address_search_button").click();
                }

                if (typeof google !== "undefined") {
                    glsMapResize();
                } else {
                    console.error('Google is not defined. Please check if an API key is set in the configuration (Stores->Configuration->Sales->GLS Advanced Shipping)');
                }
            }
            $("#gls_chosen_relay").html("");
        },

        glsSetAjaxSetRelayInformationUrl: function (AjaxSetRelayInformationUrl) {
            glsAjaxSetRelayInformationUrl = AjaxSetRelayInformationUrl;
        },

        glsPublicSetRelayId: function (relayId) {
            glsRelayId = relayId;
        },

        glsGetRelayId: function () {
            return glsRelayId;
        },

        glsGetRelayCity: function () {
            return glsRelayCity;
        },

        glsGetRelayPostCode: function () {
            return glsRelayPostCode;
        },

        glsGetRelayAddress: function () {
            return glsRelayAddress;
        },

        glsGetRelayName: function () {
            return glsRelayName;
        }
    }
});