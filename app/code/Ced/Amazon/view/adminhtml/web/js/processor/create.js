define([
        'jquery',
        'jquery/ui',
        // 'accordion',
        'lineProgressbar'
    ],
    function ($) {
        return function (config) {
            $("#amazon-progress-bar").LineProgressbar({
                percentage: 0,
                fillBackgroundColor: '#77a21b',
                height: '25px'
            });

            // $(".batch-container").accordion({collapsible: true, active: false});

            let totalRecords = parseInt(config.Total);
            let countOfSuccess = 0;
            let id = 0;
            let chunked_ids = config.ChunkedIds;
            let action_type = config.ActionType;
            let liFinished = document.getElementById('liFinished');
            let updateStatus = document.getElementById('updateStatus');
            let updateRow = document.getElementById('updateRow');
            let statusImage = document.getElementById('statusImage');
            let successImg = config.SuccessImg;
            let errorImg = config.ErrorImg;

            //call on load
            sendRequest();


            function sendRequest() {
                //console.log( parseInt(((id + 0.5) / totalRecords) * 100));
                //update progress
                $("#amazon-progress-bar").LineProgressbar({
                    percentage: parseInt(((id) / totalRecords) * 100),
                    fillBackgroundColor: '#77a21b',
                    height: '35px',
                    duration: 0
                });

                updateStatus.innerHTML = (id + 1) + ' Of ' + totalRecords + ' Processing';

                let request = $.ajax({
                    type: "GET",
                    url: config.AjaxUrl,
                    data: {batchid: id, chunked_ids: chunked_ids, action_type : action_type},
                    success: function (data) {
                        let json = data;
                        id++;
                        let span = document.createElement('li');
                        if (json.hasOwnProperty('success') && json.success) {
                            countOfSuccess++;
                            span.innerHTML =
                                '<img src="'+successImg+'"><span>' +
                                json.message + '</span>';
                            span.id = 'id-' + id;
                            updateRow.parentNode.insertBefore(span, updateRow);
                        } else {
                            let errorMessage = {
                                'status': true,
                                'errors': ''
                            };
                            if (json.hasOwnProperty('success') && !json.success) {
                                //errorMessage = json.error;
                                //console.log(parseErrors(json.messages));
                               // errorMessage = json.message;
                                //console.log(json.hasOwnProperty('count'));
                                // let heading = '<span>' +
                                //     '<img src="'+errorImg+'">'+errorMessage+'</span>';
                                // if (errorMessage.status === false && json.hasOwnProperty('count')) {
                                //     heading = '<img src="'+successImg+'"><span>' +
                                //         json.count + ' Order(s) Synced successfully</span>';
                                // }
                                //
                                // let errorTemplate = '<div class="batch-container">' +
                                //     '<div data-role="collapsible" style="cursor: pointer;">' +
                                //     '<div data-role="trigger">' + heading + '</div></div>' +
                                //     '<div data-role="content">' + errorMessage.errors + '</div></div>';
                            }
                            //span.innerHTML = '<img src="<?php  //echo $errorImg ?>"><span>' + errorMessage + '</span>';
                            span.innerHTML = '<img src="'+errorImg+'"><span>' +
                                json.message + '</span>';
                            span.id = 'id-' + id;
                            updateRow.parentNode.insertBefore(span, updateRow);
                            // $(".batch-container").accordion({collapsible: true, active: false});
                        }
                    },

                    error: function () {
                        id++;
                        let span = document.createElement('li');
                        span.innerHTML = '<img src= "'+errorImg+'"><span>Something went wrong </span>';
                        span.id = 'id-' + id;
                        //span.style = 'background-color:#FDD';
                        updateRow.parentNode.insertBefore(span, updateRow);

                    },

                    complete: function () {
                        //console.log( parseInt(((id) / totalRecords) * 100));
                        //update progress
                        $("#amazon-progress-bar").LineProgressbar({
                            percentage: parseInt(((id) / totalRecords) * 100),
                            fillBackgroundColor: '#77a21b',
                            height: '35px',
                            duration: 0
                        });

                        if (id < totalRecords) {
                            sendRequest();
                        } else {
                            statusImage.src = successImg;
                            let span = document.createElement('li');
                            span.innerHTML =
                                '<img src="'+successImg+'">' +
                                '<span id="updateStatus">' +
                                totalRecords + ' batch(s) successfully processed.' + '</span>';
                            liFinished.parentNode.insertBefore(span, liFinished);
                            document.getElementById("liFinished").style.display = "block";
                            updateStatus.innerHTML = (id) + ' of ' + totalRecords + ' Processed.';
                        }

                    },
                    dataType: "json"
                });

            }

            function parseErrors(errors) {
                let data = (errors);
                let result = {
                    'status': true,
                    'errors': ''
                };
                if (data) {
                    result.errors = '<table class="data-grid" style="margin-bottom:10px; margin-top:10px"><tr>' +
                        '<th style="padding:15px">Sl. No.</th>' +
                        '<th style="padding:15px">Sku</th>' +
                        '<th style="padding:15px">Errors</th></tr>';
                    let products = Object.keys(data).length;
                    let counter = 0;
                    $.each(data, function (index, value) {
                        let messages = '';
                        $.each(value.errors, function (i, v) {
                            if (typeof v === 'object' && v !== null && Object.keys(v).length > 0) {
                                messages += '<ul style="list-style: none;">';
                                $.each(v, function (attribute, err) {
                                    messages += '<li><b>' + attribute + '</b> : ' + err + '</li>';
                                });
                                messages += '</ul>';
                            }
                        });

                        if (messages === '') {
                            counter++;
                            messages = '<b style="color:forestgreen;">No errors.</b>';
                        }
                        if (!value['Field']) {
                            value['Field'] = value['SellerSku'];
                        }
                        //let sku = "<a href='" + value.url + "' target='_blank'>" + value.sku + "</a>";
                        result.errors += '<tr><td>' + (value['Field']) + '</td><td>' + (value['SellerSku']) + '</td><td>' + (value['Message']) +
                            '</td></tr>';
                    });
                    result.errors += '</table>';
                    if (products === counter) {
                        result.status = false;
                    }
                }
                return result;
            }

            function getPercent() {
                return Math.ceil(((id + 1) / totalRecords) * 1000) / 10;
            }
        }
    }
);