angular.module('app', ['ngRoute', 'group']);

angular.module('app').config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    $locationProvider.html5Mode(true);
    $routeProvider.otherwise({redirectTo:'/group'});
}]);

angular.module('app').controller('AppCtrl', ['$scope', function($scope){

}]);
angular.module('app').constant('API_CONFIG', {
    baseUrl: 'https://api.socializr.dev'
});
angular.module('app').controller('HeaderCtrl', ['$scope','$location', '$route', function($scope, $location, $route){
    $scope.location = $location;
    $scope.pages = [
        {"title":"group"}
    ];
}]);
angular.module('group', ['resources.groups'], ['$routeProvider', function($routeProvider){
        $routeProvider.when('/group', {
            templateUrl:'app/group/group.tpl.html',
            controller:'GroupCtrl',
            resolve:{
                groups:['Groups', function (Groups) {
                    //TODO: fetch only for the current user
                    return Groups.all();
                }]
            }
        });
    }]);
angular.module('group').controller('GroupCtrl', function ($scope, groups) {
        $scope.groups = groups;

        $scope.filteredGroups = $scope.groups;

        //sorting
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
    })

    .filter('pagination', function(){
        return function(inputArray, selectedPage, pageSize) {
            var start = selectedPage*pageSize;
            return inputArray.slice(start, start + pageSize);
        };
    });