/**
 * Created by Sander en Dorien on 30-11-13.
 */
angular.module('resources.groups', ['ngResource']);
angular.module('resources.groups').factory('Groups', ['apiResource', function (apiResource) {

    var Groups = apiResource('groups');

    return Groups;
}]);