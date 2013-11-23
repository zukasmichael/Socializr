angular.module('app', ['ngRoute', 'home']);

angular.module('app').config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    $locationProvider.html5Mode(true);
    $routeProvider.when('/home', {
        templateUrl: '/app/home/home.tpl.html'
    });
    $routeProvider.when('/group', {
        templateUrl: '/app/group/group.tpl.html'
    });
    $routeProvider.otherwise({redirectTo:'/home'});
}]);

angular.module('app').controller('AppCtrl', ['$scope', function($scope){}]);

angular.module('app').controller('HeaderCtrl', ['$scope','$location', '$route', function($scope, $location, $route){
    $scope.location = $location;
    $scope.pages = [
        {"title":"home"},
        {"title":"group"}
    ];
}]);