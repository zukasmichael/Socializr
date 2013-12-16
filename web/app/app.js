angular.module('socializrApp', ['ngRoute', 'auth', 'home', 'groups', 'users', 'ui.bootstrap']);

angular.module('socializrApp').constant('API_CONFIG', {
    baseUrl: 'https://api.socializr.io'
});

angular.module('socializrApp')
    .config(['$routeProvider', '$locationProvider', '$httpProvider', function ($routeProvider, $locationProvider, $httpProvider) {
        var access = routingConfig.accessLevels;

        $locationProvider.html5Mode(true);
        $routeProvider.otherwise({redirectTo: '/home'});
        $httpProvider.defaults.withCredentials = true;

        $httpProvider.interceptors.push(function($q, $location) {
            return {
                'responseError': function(response) {
                    if(response.status === 401 || response.status === 403) {
                        $location.path('/user/login');
                        return $q.reject(response);
                    }
                    else {
                        return $q.reject(response);
                    }
                }
            }
        });
    }]);
angular.module('socializrApp').run(['$rootScope', '$location', '$http', 'Auth', function ($rootScope, $location, $http, Auth) {

    $rootScope.$on("$routeChangeStart", function (event, next, current) {
        $rootScope.error = null;
        if (!Auth.authorize(next.access)) {
            if(Auth.isLoggedIn()){
                $location.path('/');
            } else {
                $location.path('/login');
            }
        }
    });

}])
angular.module('socializrApp').controller('AppCtrl', ['$scope', function ($scope) {

}]);
angular.module('socializrApp')
.controller('HeaderCtrl', ['$rootScope', '$scope', '$location', '$route', 'Auth', function ($rootScope, $scope, $location, $route, Auth) {
    $scope.location = $location;
    $scope.user = Auth.user;
    $scope.userRoles = Auth.userRoles;
    $scope.accessLevels = Auth.accessLevels;

    $scope.logout = function() {
        Auth.logout(function() {});
    };
    $scope.isActive = function (viewLocation) {
        return viewLocation === $location.path();
    };
}]);

angular.module('auth', ['ngRoute']);

angular.module('home', []);

angular.module('home').config(['$routeProvider', function ($routeProvider) {
    var access = routingConfig.accessLevels;
    $routeProvider.
        when('/home', {
            templateUrl: '/app/home/home.tpl.html',
            controller: 'HomeCtrl',
            access: access.public
        });
}]);
angular.module('socializrApp').controller('HomeCtrl', ['$rootScope', '$scope', 'Auth', function ($rootScope, $scope, Auth) {
    Auth.login(
        function(user) {
            $scope.user = user;
        },
        function(err) {
            //$rootScope.error = "Failed to login";
        });
}]);

angular.module('groups', ['resources.groups'])
    .config(['$routeProvider', function ($routeProvider) {
        var access = routingConfig.accessLevels;
        $routeProvider.when('/groups', {
            templateUrl: '/app/group/group.tpl.html',
            controller: 'GroupListCtrl',
            resolve: {
                groups: ['Groups', function (Groups) {
                    return Groups.all();
                }]
            },
            access: access.public
        });
        $routeProvider.when('/groups/new', {
            templateUrl: '/app/group/edit.tpl.html',
            controller: 'GroupNewCtrl',
            access: access.user
        });
        $routeProvider.when('/groups/:groupId', {
            templateUrl: '/app/group/details.tpl.html',
            controller: 'GroupDetailCtrl',
            access: access.public
        });
    }])
    .controller('GroupListCtrl', ['$rootScope', '$scope', '$location', 'groups',
        function ($rootScope, $scope, $location, groups) {
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
            $scope.$watch('filteredGroups.length', function (filteredSize) {
                $scope.pages.length = 0;
                var noOfPages = Math.ceil(filteredSize / $scope.pageSize);
                for (var i = 0; i < noOfPages; i++) {
                    $scope.pages.push(i);
                }
            });

            $scope.setActivePage = function (pageNo) {
                if (pageNo >= 0 && pageNo < $scope.pages.length) {
                    $scope.pageNo = pageNo;
                }
            };

            $scope.view = function(group){
                $location.path('/groups/' + group.id);
            };
        }]
    ).filter('pagination', function () {
        return function (inputArray, selectedPage, pageSize) {
            var start = selectedPage * pageSize;
            return inputArray.slice(start, start + pageSize);
        };
    });
angular.module('groups').controller('GroupDetailCtrl', ['$rootScope', '$scope', '$routeParams', '$http',
    function ($rootScope, $scope, $routeParams, $http) {
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId).success(function (data) {
            $scope.group = data;
        });
    }]
);
angular.module('groups').controller('GroupNewCtrl', ['$rootScope', '$scope', '$location', '$routeParams', '$http', 'Auth',
    function ($rootScope, $scope, $location, $routeParams, $http, Auth) {
        $scope.group = {};
        $scope.user = Auth.user;

        $scope.options = [
            { name: 'open', value: 1},
            { name: 'besloten', value: 2},
            { name: 'geheim', value: 3}
        ];

        $scope.addGroup = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            $scope.group.admins = [{id:$scope.user.id, userName: $scope.user.user_name, email: $scope.user.email, roles: $scope.user.roles}];
            $http.post("https://api.socializr.io/group", $scope.group)
                .success(function (data, status, headers, config) {
                    $scope.group = data;
                }).error(function (data, status, headers, config) {
                    console.log(status);
                });
        };
    }]
);

angular.module('users', []);
angular.module('users')
    .config(['$routeProvider', function ($routeProvider) {
        var access = routingConfig.accessLevels;
        $routeProvider.when('/users/profile', {
            templateUrl: '/app/user/profile.tpl.html',
            controller: 'UserProfileCtrl',
            access: access.public
        });
        $routeProvider.when('/users/login', {
            templateUrl: '/app/user/login.tpl.html',
            controller: 'UserLoginCtrl',
            access: access.public
        });
    }])
    .controller('UserProfileCtrl', ['$scope', '$http', 'Auth',
        function ($scope, $http, Auth) {
            Auth.login(
                function(user) {
                    $scope.user = user;
                },
                function(err) {
                    //$rootScope.error = "Failed to login";
                });
        }])
    .controller('UserLoginCtrl', ['$scope', '$http',
        function ($scope, $http) {
            $http.get("https://api.socializr.io/login")
                .success(function (data) {
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