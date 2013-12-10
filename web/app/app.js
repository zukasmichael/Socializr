var socializrApp = angular.module('socializrApp', [
    'socializrControllers'
]);
socializrApp.constant('API_CONFIG', {
    baseUrl: 'https://api.socializr.io'
});

var socializrControllers = angular.module('socializrControllers', ['resources.groups', 'ngRoute', 'ngCookies'])
    .config(['$routeProvider', '$httpProvider',  function($routeProvider, $httpProvider) {
        $httpProvider.defaults.withCredentials = true;
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
                templateUrl: 'app/group/details.tpl.html',
                controller: 'GroupDetailCtrl'
            }).
            when('/group/new/', {
                templateUrl: 'app/group/edit.tpl.html',
                controller: 'GroupNewCtrl'
            }).
            when('/user/profile/', {
                templateUrl: 'app/user/profile.tpl.html',
                controller: 'UserProfileCtrl'
            }).
            when('/user/login/', {
                templateUrl: 'app/user/login.tpl.html',
                controller: 'UserLoginCtrl'
            }).
            otherwise({
                redirectTo: '/groups'
            });
    }])
    .filter('pagination', function(){
        return function(inputArray, selectedPage, pageSize) {
            var start = selectedPage*pageSize;
            return inputArray.slice(start, start + pageSize);
        };
    });
socializrControllers.controller('AppCtrl', ['$scope', function($scope){

}]);
socializrControllers.controller('HeaderCtrl', ['$scope','$location', '$route', function($scope, $location, $route){
    $scope.location = $location;
    $scope.pages = [
        {"uri": "groups", "title":"Groepen"},
        {"uri": "group/new", "title":"Nieuwe Groep"},
        {"uri": "user/profile", "title":"Profiel"}
    ];
}]);
socializrControllers.controller('GroupListCtrl', ['$scope', 'groups',
    function ($scope, groups) {
        $scope.groups = groups;
        $scope.filteredGroups = $scope.groups;
        $scope.sortField = undefined;
        $scope.reverse = false;

        $scope.sort = function (fieldName) {
            if ($scope.sortField === fieldName) {
                $scope.reverse = !$scope.reverse;
            } else {
                $scope.sortField = fieldName;
                $scope.reverse = false;
            }
        };
        $scope.isSortUp = function (fieldName) {
            return $scope.sortField === fieldName && !$scope.reverse;
        };
        $scope.isSortDown = function (fieldName) {
            return $scope.sortField === fieldName && $scope.reverse;
        };

        //pagination
        $scope.pageSize = 3;
        $scope.pages = [];
        $scope.$watch('filteredGroups.length', function(filteredSize){
            $scope.pages.length = 0;
            var noOfPages = Math.ceil(filteredSize / $scope.pageSize);
            for (var i=0; i<noOfPages; i++) {
                $scope.pages.push(i);
            }
        });

        $scope.setActivePage = function (pageNo) {
            if (pageNo >=0 && pageNo < $scope.pages.length) {
                $scope.pageNo = pageNo;
            }
        };
    }]
);
socializrControllers.controller('GroupDetailCtrl', ['$scope', '$routeParams', '$http',
    function($scope, $routeParams, $http) {
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId).success(function(data){
            $scope.group = data;
        });
    }]
);
socializrControllers.controller('GroupNewCtrl', ['$scope', '$http',
    function($scope, $http) {
        $scope.group = {};
        $scope.options = [
            { name: 'open', value: 1},
            { name: 'besloten', value: 2},
            { name: 'geheim', value: 3}];

        $scope.addGroup = function () {
            $http.post("https://api.socializr.io/group/", $scope.group)
            .success(function (data, status, headers, config) {
                $scope.group = data;
            }).error(function (data, status, headers, config) {
                //throw new Error('Something went wrong...');
            });
        };
    }]
);
socializrControllers.controller('UserProfileCtrl', ['$scope',
    function ($scope) {
        $scope.user = {name:"Sander Groen"};
    }]
);socializrControllers.controller('UserLoginCtrl', ['$scope', '$cookies', '$http',
    function ($scope, $cookies, $http) {
        $http.get("https://api.socializr.io/login")
            .success(function(data){
                $scope.logins = data.loginPaths;
        });

        $scope.loginFacebook = function () {
            window.location = "https://api.socializr.io" + $scope.logins.facebook;
        };

        $scope.loginTwitter = function () {
            window.location = "https://api.socializr.io" + $scope.logins.twitter;
        };

        $scope.loginGoogle = function () {
            window.location = "https://api.socializr.io" + $scope.logins.google;
        };
    }]
);