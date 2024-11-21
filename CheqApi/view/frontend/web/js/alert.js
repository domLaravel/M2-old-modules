define([
    "jquery", "domReady!"
], function ($) {
    "use strict";

    return function (config) {

        //check to see if we're on ko rendered page
        let isCheckoutShippingPage = window.location.href.indexOf('checkout') !== -1 || window.location.href.indexOf('#shipping') !== -1;
        let keyValueArray = getElementArray(config.myValue);
        let koIdentifiers = [];

        if(isCheckoutShippingPage){
            $.each(keyValueArray, function (key, value) {
                if ("isKo" in value) {
                    let elementIdentifier = value['key'];
                    koIdentifiers.push(elementIdentifier);
                }
            });

            $.each(koIdentifiers, function (index, elementIdentifier) {
                let targetElementSelector = elementIdentifier;
                let targetElement = $(targetElementSelector);

                if (targetElement.length) {
                    // Element is present
                    sendData(keyValueArray);
                } else {
                    // Element not present, wait for it to be available
                    let checkElementInterval = setInterval(function () {
                        targetElement = $(targetElementSelector);
                        if (targetElement.length) {
                            sendData(keyValueArray);
                            clearInterval(checkElementInterval);
                        }
                    }, 100);
                }
            });
        } else {
            let keyValueArray = getElementArray(config.myValue);
            sendData(keyValueArray);
        }

        const currentHostname = window.location.origin; // Gets the origin (protocol + hostname + port)

        if(localStorage.getItem('cheqcookieglobalblock') === true){
            window.location.href = `${currentHostname}/404-not-found`;
        }

        if (config.myValue2) {
            try {
                let jqueryCode = config.myValue2;
                let dynamicFunction = new Function(jqueryCode);
                dynamicFunction();
            } catch (error) {
                console.error('Error executing dynamic function:', error);
            }
        } else {
            console.error('config.myValue2 is not defined');
        }


    };

    function getElementArray(cvalue) {
        let keyValueArray = [];

        if (cvalue) {
            let splitArray = cvalue.split(",");
            keyValueArray = splitArray.map(item => {
                let [key, value] = item.split("=");
                if (value.includes("*ko")) {
                    var [eventType, url, identifier] = value.split("|");
                    url = url.split("*")[0];
                    return { key, eventType, url, isKo: "true" };
                } else {
                    var [eventType, url] = value.split("|");
                    return { key, eventType, url };
                }
            });
        } else {
            console.error("Data supplied in config.myValue is malformed/incorrect.");
        }

        return(keyValueArray);
    }

    function sendData(keyValueArray) {
        keyValueArray.forEach(function (selector) {
            let elements = $(selector['key']);
            if (elements.length) {
                elements.on('click', function (event) {
                    $.ajax({
                        url: location.protocol + '//' + location.host + "/buildingmaterials/cheqapi/sendrequest",
                        type: 'POST',
                        data: {chequrl: selector['key'], eventType: selector['eventType'], eventUrl: selector['url']},
                        dataType: 'json',
                        success: function (data, status, xhr) {
                            if (data != null) {
                                let rData = JSON.parse(data);
                                if (rData[0] === 'redirect') {
                                    document.cookie = "threatType=" + rData;
                                    let baseUrl = window.location.protocol + "//" + window.location.host + '/';
                                    window.location.href = baseUrl + rData[1];
                                } else {
                                    setCookie(data);
                                }
                            }
                        },
                        error: function (xhr, status, errorThrown) {
                            console.error('There has been an error with cheqAPI: ' + errorThrown);
                        }
                    });
                });
            }
        });
    }

    function setCookie(cookie) {
        try {
            let cookieObject = JSON.parse(cookie);
            cookieObject.Duration = new Date(cookieObject.Duration).toUTCString();
            document.cookie = cookieObject.Name + '=' + cookieObject.Value + '; Expires=' + cookieObject.Duration + '; Domain=' + cookieObject.Domain + '; Path=' + cookieObject.Path;
        } catch (error) {
            console.error('Error setting cookie: ', error);
        }
    }
});