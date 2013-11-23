angular.module('group', [], ['$routeProvider', function($routeProvider){
    $routeProvider.when('/group', {
        templateUrl:'group/group.tpl.html',
        controller:'GroupCtrl'
    });
}]);

angular.module('group').controller('GroupCtrl', ['$scope', function($scope){
}]);