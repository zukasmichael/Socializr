var socializrApp = angular.module('socializrApp', [
    'socializrControllers'
]);
socializrApp.constant('API_CONFIG', {
    baseUrl: 'https://api.socializr.io'
});

var socializrControllers = angular.module('socializrControllers', ['resources.groups', 'ngRoute'])
    .config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/groups', {
                templateUrl: 'app/group/group.tpl.html',
                controller: 'GroupListCtrl',
                resolve:{
                    groups:['Groups', function (Groups) {
                        return Groups.all();
                    }]
                }
            }).
            when('/groups/:groupId', {
                templateUrl: 'app/group/edit.tpl.html',
                controller: 'GroupDetailCtrl'
            }).
            otherwise({
                redirectTo: '/groups'
            });
    }]);
socializrControllers.controller('GroupListCtrl', ['$scope', 'groups',
    function ($scope, groups) {
        $scope.groups = groups;
    }]
);
socializrControllers.controller('GroupDetailCtrl', ['$scope', '$routeParams', '$http',
    function($scope, $routeParams, $http) {
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId).success(function(data){
            $scope.group = data;
        });
    }]
);