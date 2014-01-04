angular.module('socializrApp', ['ngRoute', 'auth', 'home', 'groups', 'users', 'boards', 'profiles', 'ui.bootstrap']);

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

}]);
angular.module('socializrApp').controller('AppCtrl', ['$scope', 'Auth', function ($scope, Auth) {
    Auth.login(
        function(user) {
            $scope.user = user;
        },
        function(err) {
            //$rootScope.error = "Failed to login";
        });
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
angular.module('groups').controller('GroupDetailCtrl', ['$rootScope', '$scope', '$routeParams', '$http', 'Auth', '$location',
    function ($rootScope, $scope, $routeParams, $http, Auth, $location) {
        $scope.user = Auth.user;

        $scope.permissions = {
            loggedin: ($scope.user.role === Auth.userRoles.user) || $scope.user.role === Auth.userRoles.admin
        };

        $scope.addBoard = function(){
            $location.path('/boards/new/' + $scope.group.id);
        };

        $http.get("https://api.socializr.io/group/" + $routeParams.groupId).success(function (data) {
            $scope.group = data;
        });
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId + "/board").success(function (data) {
            $scope.boards = data;
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
            $http.post("https://api.socializr.io/group/", $scope.group)
                .success(function (data, status, headers, config) {
                    $location.path('/groups');
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
            access: access.user
        });
        $routeProvider.when('/users/login', {
            templateUrl: '/app/user/login.tpl.html',
            controller: 'UserLoginCtrl',
            access: access.public
        });
    }])
    .factory('profileService', function($http) {
        groups = [];
        var profileService = function(){
            groups = [];
        };
        profileService.prototype.getGroups = function(offset, limit) {
            $http.get('https://api.socializr.io/user/current/news?limit='+limit + '&offset=' + offset)
                .success(function(data){
                    var items = data;
                    for(var i =0; i < data.length; i++){
                        groups.push(data[i]);
                    }
                });
            return groups;
        };

        profileService.prototype.count = function() {
            return groups.length;
        };
        return profileService;
    })
    .controller('UserProfileCtrl', ['$scope', '$http', 'Auth', 'profileService',
        function ($scope, $http, Auth, profileService) {
            $scope.profileService = new profileService();

            $scope.numPerPage = 6;
            $scope.currentPage = 1;

            $scope.nextPage = function(){
                $scope.currentPage++;
            };

            $scope.setPage = function () {
                $scope.groups = $scope.profileService.getGroups( ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage );
            };

            $scope.$watch( 'currentPage', $scope.setPage );
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
    ).directive('whenScrolled', function() {
        return function(scope, elm, attr) {
            var raw = elm[0];

            elm.bind('scroll', function() {
                if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight) {
                    scope.$apply(attr.whenScrolled);
                }
            });
        };
    });
angular.module('profiles', []);
angular.module('profiles')
.config(['$routeProvider', function ($routeProvider) {
    var access = routingConfig.accessLevels;
    $routeProvider.when('/profiles/:profileId', {
        templateUrl: '/app/profile/view.tpl.html',
        controller: 'ProfileViewCtrl',
        access: access.user
    });
    $routeProvider.when('/profiles/edit/:profileId', {
        templateUrl: '/app/profile/edit.tpl.html',
        controller: 'ProfileEditCtrl',
        access: access.user
    });
}])
    .controller('ProfileViewCtrl', ['$scope', '$http', '$routeParams', 'Auth', '$location', function($scope, $http, $routeParams, Auth, $location){
        $http.get("https://api.socializr.io/profiles/" + $routeParams.profileId)
            .success(
            function(data){
                $scope.profile = data;
            }
        );
        $scope.interests = function(){
            var interests = '';
            var cnt = 0;
            $scope.profile.interests.forEach(function(entry) {
                interests = interests + entry.interest;
                if(cnt < $scope.profile.interests.length -1){
                    interests = interests + ",";
                }
                cnt++;
            });
            return interests;
        };
    }])
    .controller('ProfileEditCtrl', ['$scope', '$http', '$routeParams', 'Auth', '$location', '$timeout', function($scope, $http, $routeParams, Auth, $location, $timeout){

        $http.get("https://api.socializr.io/profiles/" + $routeParams.profileId)
            .success(function(data){
                $scope.profile = data;
            }
        );

        $scope.add = function(){
            $scope.profile.interests.push({interest:''});
        };

        $scope.remove = function(index) {
            $scope.profile.interests.splice(index, 1);
        };

        $scope.save = function(profile){
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log(profile);
            $http.post("https://api.socializr.io/profiles/" + $routeParams.profileId, profile)
                .success(function (data, status, headers, config) {
                    $scope.profile = data;
                }
            ).error(function (data, status, headers, config) {
                    console.log(status);
                });
        };

        $scope.today = function () {
            $scope.dt = new Date();
        };
        $scope.today();

        $scope.showWeeks = true;
        $scope.toggleWeeks = function () {
            $scope.showWeeks = !$scope.showWeeks;
        };

        $scope.clear = function () {
            $scope.dt = null;
        };

        // Disable weekend selection
        $scope.disabled = function (date, mode) {
            return ( mode === 'day' && ( date.getDay() === 0 || date.getDay() === 6 ) );
        };

        $scope.toggleMin = function () {
            $scope.minDate = ( $scope.minDate ) ? null : new Date();
        };

        $scope.toggleMin();

        $scope.toggleMax = function () {
            var now = new Date();
            $scope.maxDate = now;
        };

        $scope.toggleMax();

        $scope.fromopen = function () {
            $timeout(function () {
                $scope.fromopened = true;
            });
        };
        $scope.toopen = function () {
            $timeout(function () {
                $scope.toopened = true;
            });
        };
        $scope.dateOptions = {
            'year-format': "'yyyy'",
            'starting-day': 1
        };

        $scope.formats = ['dd-MM-yyyy'];
        $scope.format = $scope.formats[0];
    }]);
angular.module('boards', []).config(['$routeProvider', function ($routeProvider) {
    var access = routingConfig.accessLevels;
    $routeProvider.when('/boards/new/:groupId', {
        templateUrl: '/app/boards/edit.tpl.html',
        controller: 'BoardNewController',
        access: access.user
    });
    $routeProvider.when('/boards/:boardId', {
        templateUrl: '/app/boards/details.tpl.html',
        controller: 'BoardDetailsController',
        access: access.public
    });
}]);

angular.module('boards').controller('BoardNewController', ['$scope', '$http', '$routeParams', 'Auth', '$location', function($scope, $http, $routeParams, Auth, $location){
    $scope.board;
    $scope.groupId = $routeParams.groupId;
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

    $scope.addBoard = function(){
        $http.post("https://api.socializr.io/group/" + $scope.groupId +'/board', $scope.board)
            .success(function (data, status, headers, config) {
                $location.path('/groups/' + $scope.groupId);
            }).error(function (data, status, headers, config) {
                console.log(status);
            });
    };
}]);

angular.module('boards').controller('BoardDetailsController', ['$scope', '$http', '$routeParams', 'Auth', '$route', function($scope, $http, $routeParams, Auth, $route){
    $scope.boardId = $routeParams.boardId;
    $scope.message;
    $scope.user = Auth.user;

    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

    $http.get("https://api.socializr.io/board/" + $routeParams.boardId).success(function (data) {
        $scope.board = data;
    });

    $http.get("https://api.socializr.io/board/" + $routeParams.boardId + '/message').success(function (data) {
        $scope.messages = data;
    });

    $scope.md2Html = function() {
        return $scope.html = $window.marked($scope.markdown);
    };

    $scope.initFromUrl = function(url) {
        return $http.get(url).success(function(data) {
            $scope.markdown = data;
            return $scope.md2Html();
        });
    };

    $scope.permissions = {
        loggedin: ($scope.user.role === Auth.userRoles.user) || $scope.user.role === Auth.userRoles.admin
    };

    $scope.addMessage = function(){
        console.log('addMesage');
        $http.post("https://api.socializr.io/board/" + $scope.boardId +'/message', $scope.message)
            .success(function (data, status, headers, config) {
                $route.reload();
            }).error(function (data, status, headers, config) {
                console.log(status);
            });
    };

    return $scope.initFromText = function(text) {
        $scope.markdown = text;
        return $scope.md2Html();
    };
}]);