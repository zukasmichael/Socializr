(function () {
    // Turn off strict mode for this function so we can assign to global.
    /* jshint strict: false */

    apiUri = 'http://testapi.socializr.io';

    getFullApiUrl = function(partialUri) {
        return apiUri + partialUri;
    }
})();