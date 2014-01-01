(function () {
    // Turn off strict mode for this function so we can assign to global.
    /* jshint strict: false */

    apiUri = 'http://testapi.socializr.io';

    getFullApiUrl = function(partialUri) {
        return apiUri + partialUri;
    };

    lastResults = [];
    dataHandlers = [];
    saveData = function(uri, json) {
        lastResults[uri] = json;
        return json;
    };
    getDataByUrl = function(uri, method, data, forceAjax) {
        method = method ? method : 'GET';
        saveUri = method + ':' + uri;

        console.log(method, saveUri);
        if (!forceAjax && lastResults[saveUri]) {
            var deferred = Q.defer();
            setTimeout(deferred.resolve(lastResults[saveUri]), 0);
            return deferred.promise;
        }

        return Q.promise(function (resolve) {
            options = {
                type: method,
                url: getFullApiUrl(uri),
                dataType: 'json'
            };

            if (data) {
                options.data = data;
            }

            jQuery.ajax(options).then(
                function (data, textStatus, jqXHR) {
                    delete jqXHR.then; // treat xhr as a non-promise

                    for (var prop in dataHandlers) {
                        if (dataHandlers.hasOwnProperty(prop)) {
                            dataHandlers[prop](data);
                        }
                    }
                    data = saveData(saveUri, data);

                    resolve(data);
                }, function (jqXHR, textStatus, errorThrown) {
                    delete jqXHR.then; // treat xhr as a non-promise

                    resolve(jqXHR);
                }
            );
        });
    };
})();