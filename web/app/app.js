angular.module('socializrApp', ['ngRoute', 'auth', 'home', 'groups', 'users', 'boards', 'profiles', 'markdown', 'ui.bootstrap']);

angular.module('socializrApp').constant('API_CONFIG', {
    baseUrl: 'https://api.socializr.io'
});

angular.module('socializrApp')
    .factory('searchService', function($http) {
        var results = [];
        var profileService = function(){
            results = [];
        };
        profileService.prototype.getResults = function(type, offset, limit, query) {
            $http.get('https://api.socializr.io/search/' + query + '?limit='+limit + '&offset=' + offset + '&type=' + type)
                .success(function(data){
                    var items;
                    if(type==='groups'){
                        items = data.groups;
                    }
                    if(type==='users'){
                        items = data.users;
                    }
                    for(var i =0; i < items.length; i++){
                        results.push(items[i]);
                    }
                });
            return results;
        };
        profileService.prototype.search = function(type, offset, limit, query) {
            results = [];
            $http.get('https://api.socializr.io/search/' + query + '?limit='+limit + '&offset=' + offset + '&type=' + type)
                .success(function(data){
                    var items;
                    if(type==='groups'){
                        items = data.groups;
                    }
                    if(type==='users'){
                        items = data.users;
                    }
                    for(var i =0; i < items.length; i++){
                        results.push(items[i]);
                    }
                });
            return results;
        };
        profileService.prototype.count = function() {
            return results.length;
        };
        return profileService;
    })
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
    $scope.hashtag = 'dwdd';
}]);

angular.module('groups', [])
    .config(['$routeProvider', function ($routeProvider) {
        var access = routingConfig.accessLevels;
        $routeProvider.when('/groups', {
            templateUrl: '/app/group/group.tpl.html',
            controller: 'GroupListCtrl',
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
        $routeProvider.when('/groups/admin/:groupId', {
            templateUrl: '/app/group/admin.tpl.html',
            controller: 'GroupAdminCtrl',
            access: access.public
        });
    }])
    .controller('GroupListCtrl', ['$rootScope', '$scope', '$location', 'searchService',
        function ($rootScope, $scope, $location, searchService) {
            $scope.searchService = new searchService();
            $scope.criteria = ' ';
            $scope.numPerPage = 15;
            $scope.currentPage = 1;

            $scope.nextPage = function(){
                $scope.currentPage++;
            };

            $scope.setPage = function () {
                $scope.groups = $scope.searchService.getResults('groups', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
            };

            $scope.$watch( 'currentPage', $scope.setPage );

            $scope.view = function(group){
                $location.path('/groups/' + group.id);
            };

            $scope.search = function(){
                if($scope.criteria == ''){
                    $scope.criteria = ' ';
                }
                $scope.numPerPage = 15;
                $scope.currentPage = 1;
                $scope.groups = $scope.searchService.search('groups', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
            };
        }]
    );
angular.module('groups').controller('GroupDetailCtrl', ['$rootScope', '$scope', '$routeParams', '$http', 'Auth', '$location', '$route',
    function ($rootScope, $scope, $routeParams, $http, Auth, $location, $route) {
        $scope.user = Auth.user;
        $scope.note;

        $scope.isLoggedIn = function(){
            var isAdmin = false;
            var isLoggedIn = false;
            angular.forEach($scope.user.permissions, function(entry) {
                if(entry.group_id === $scope.group.id){
                    isAdmin = entry.access_level == 5;
                    isLoggedIn = entry.access_level > 0;
                }
            });
            return {
                isGroupAdmin : isAdmin,
                isLoggedIn : isLoggedIn
            };
        };

        $scope.admin = function(){
            $location.path('/groups/admin/' + $scope.group.id);
        };

        $scope.addBoard = function(){
            $location.path('/boards/new/' + $scope.group.id);
        };

        $scope.addNote = function(note){
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            $http.post('https://api.socializr.io/group/' + $scope.group.id + '/note', note)
                .success(function (data) {
                    $route.reload();
                });
        };
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId).success(function (data) {
            $scope.group = data;
            if($scope.group.hashtag != null){
                $scope.hashtag = $scope.group.hashtag;
            }
        });

        $scope.$watch('hashtag', function() {
            if($scope.hashtag != undefined){
                $http.get("https://api.socializr.io/twitter/" + $scope.hashtag +'?limit=6')
                    .success(function (data) {
                        $scope.twitterfeed = data;
                });
            }
        });

        $http.get("https://api.socializr.io/group/" + $routeParams.groupId + "/board").success(function (data) {
            $scope.boards = data;
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

        return $scope.initFromText = function(text) {
            $scope.markdown = text;
            return $scope.md2Html();
        };
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
angular.module('groups').controller('GroupAdminCtrl', ['$rootScope', '$scope', '$location', '$routeParams', '$http', 'Auth', '$route', 'searchService',
    function ($rootScope, $scope, $location, $routeParams, $http, Auth, $route, searchService) {
        $scope.groupId = $routeParams.groupId;
        $scope.user = Auth.user;
        $scope.searchService = new searchService();
        $scope.criteria = ' ';
        $scope.numPerPage = 16;
        $scope.currentPage = 1;

        $http.get("https://api.socializr.io/group/" + $routeParams.groupId)
            .success(function(data){
                $scope.group = data;
            }).error(function(){
                $location.path('/groups/' + $scope.groupId);
            });

        $scope.nextPage = function(){
            $scope.currentPage++;
        };

        $scope.setPage = function () {
            $scope.users = $scope.searchService.getResults('users', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
        };

        $scope.search = function(){
            $scope.numPerPage = 16;
            $scope.currentPage = 1;
            if($scope.criteria == ''){
                $scope.criteria = ' ';
            }
            $scope.users = $scope.searchService.search('users', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
        };

        $scope.$watch( 'currentPage', $scope.setPage );

        $http.get("https://api.socializr.io/group/" + $routeParams.groupId + '/permissions/5')
        .success(function(data){
            $scope.admins = data;
        }).error(function(){
            $location.path('/groups/' + $scope.groupId);
        });
        $http.get("https://api.socializr.io/group/" + $routeParams.groupId + '/permissions/1').success(function(data){
            $scope.members = data;
        });
        $http.get("http://api.socializr.io/user/").success(function(data){
            $scope.users = data;
        });

        $scope.invite = function(user){
            $http.get('https://api.socializr.io/group/' + $routeParams.groupId +'/invite/' + user.id)
                .success(function (data) {
                    $route.reload();
                }
            );
        };

        $scope.promote = function(user){
            $http.get('https://api.socializr.io/group/' + $routeParams.groupId +'/promote/' + user.id)
                .success(function (data) {
                    $route.reload();
                }
            );
        };

        $scope.ban = function(user){
            $http.get('https://api.socializr.io/group/' + $routeParams.groupId +'/block/' + user.id)
                .success(function (data) {
                    $route.reload();
                }
            );
        };

        $scope.saveHashtag = function(group){
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            $http.post("https://api.socializr.io/group/" + $routeParams.groupId, group)
                .success(function (data, status, headers, config) {
                    $scope.group = data;
                }
            ).error(function (data, status, headers, config) {
                    console.log(status);
            });
        };
    }
]);
angular.module('users', []);

angular.module('users')
    .config(['$routeProvider', function ($routeProvider) {
        var access = routingConfig.accessLevels;
        $routeProvider.when('/users/profile', {
            templateUrl: '/app/user/profile.tpl.html',
            controller: 'UserProfileCtrl',
            access: access.user
        });
        $routeProvider.when('/users/groups', {
            templateUrl: '/app/user/groups.tpl.html',
            controller: 'UserGroupsCtrl',
            access: access.user
        });
        $routeProvider.when('/users/login', {
            templateUrl: '/app/user/login.tpl.html',
            controller: 'UserLoginCtrl',
            access: access.public
        });
        $routeProvider.when('/users/search', {
            templateUrl: '/app/user/list.tpl.html',
            controller: 'UserListCtrl',
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
        profileService.prototype.getAllGroups = function(limit){
            groups = [];
            $http.get('https://api.socializr.io/user/current/news?limit='+limit)
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
    .controller('UserProfileCtrl', ['$scope', '$http', 'Auth', 'profileService', '$location', '$timeout',
        function ($scope, $http, Auth, profileService, $location, $timeout) {
            $http.get("https://api.socializr.io/user/" + $scope.user.id + '/profile')
                .success(
                function(data){
                    $scope.profile = data;
                }
            );
            $scope.disable = function(){
                var deleteUser = confirm('Weet je zeker dat je je account wilt verwijderen?');
                if (deleteUser) {
                    window.location = "https://api.socializr.io/user/current/disable";
                }
            };
            $scope.edit = function(){
                $location.path('/profiles/edit/' + $scope.user.profile_id);
            };
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

            $scope.profileService = new profileService();

            $scope.numPerPage = 9;
            $scope.currentPage = 1;

            $scope.nextPage = function(){
                $scope.currentPage++;
            };

            $scope.setPage = function () {
                $scope.groups = $scope.profileService.getGroups( ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage );
            };

            $scope.$watch( 'currentPage', $scope.setPage );
        }])
    .controller('UserGroupsCtrl', ['$scope', '$http', function ($scope, $http) {
        $http.get("https://api.socializr.io/user/current/groups")
            .success(function (data) {
                $scope.groups = data;
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
    ).directive('whenScrolled', function() {
        return function(scope, elm, attr) {
            var raw = elm[0];
            var currHeight = 0;
            var newScrollData = false;
            var watchVar = null;

            //Sderooij: new data scroll for whole page
            if (scope.groups) {
                watchVar = 'groups';
            } else if (scope.users) {
                watchVar = 'users';
            }

            if (watchVar) {
                scope.$watch(watchVar, function(newValue, oldValue) {
                    if (newValue) {
                        newScrollData = true;
                        if (currHeight >= 0) {
                            var elHeight = jQuery(elm).height();
                            if (currHeight < elHeight && elHeight < jQuery('body').height()) {
                                currHeight = elHeight;
                                setTimeout(function(){
                                    scope.$apply(attr.whenScrolled);
                                }, 0);
                            }
                        }
                    }
                }, true);
            }

            jQuery(window).scroll(function() {
                currHeight = -1;
                if (newScrollData && (raw.scrollTop + raw.offsetHeight) >= raw.scrollHeight) {
                    newScrollData = false;
                    scope.$apply(attr.whenScrolled);
                }
            });
        };
    })
    .controller('UserListCtrl', ['$rootScope', '$scope', '$location', 'searchService',
        function ($rootScope, $scope, $location, searchService) {
            $scope.searchService = new searchService();
            $scope.criteria = ' ';
            $scope.numPerPage = 16;
            $scope.currentPage = 1;

            $scope.nextPage = function(){
                $scope.currentPage++;
            };

            $scope.setPage = function () {
                $scope.users = $scope.searchService.getResults('users', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
            };

            $scope.$watch( 'currentPage', $scope.setPage );

            $scope.view = function(user){
                $location.path('/users/' + user.id);
            };

            $scope.search = function(){
                if($scope.criteria == ''){
                    $scope.criteria = ' ';
                }
                $scope.numPerPage = 16;
                $scope.currentPage = 1;
                $scope.users = $scope.searchService.search('users', ($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.criteria);
            };
        }]
    );
angular.module('profiles', []);
angular.module('profiles')
.config(['$routeProvider', function ($routeProvider) {
    var access = routingConfig.accessLevels;
    $routeProvider.when('/profiles/:userId', {
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
        $http.get("https://api.socializr.io/user/" +  $routeParams.userId).success(
            function(data){
                $scope.user = data;
            }
        );

        $http.get("https://api.socializr.io/user/" +  $routeParams.userId + '/profile').success(
            function(data){
                $scope.profile = data;
                var interests = '';
                $scope.profile.interests.forEach(function(entry) {
                        interests = interests + entry.interest + ",";
                });
                $scope.interests= interests;
            }
        );
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
                    $location.path('/users/profile/');
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

angular.module('boards', ['resources.messages']).config(['$routeProvider', function ($routeProvider) {
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

angular.module('boards').controller('BoardDetailsController', ['$scope', '$http', '$routeParams', 'Auth', '$route', 'messageService', function($scope, $http, $routeParams, Auth, $route, messageService){
    $scope.boardId = $routeParams.boardId;
    $scope.message;
    $scope.user = Auth.user;
    $scope.numPerPage = 6;
    $scope.currentPage = 1;

    $scope.messageService = new messageService();

    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

    $http.get("https://api.socializr.io/board/" + $routeParams.boardId).success(function (data) {
        $scope.board = data;
        $scope.breadcrumbs = [
            {"path" : '/groups', "name": 'Groepen'},
            {"path" : '/groups/'+data.group_id, "name": data.group_name},
            {"path" : '/boards/'+data.id, "name": data.title}
        ];
    });

    $scope.nextPage = function(){
        $scope.currentPage++;
    };

    $scope.setPage = function () {
        $scope.messages = $scope.messageService.getMessages(($scope.currentPage - 1) * $scope.numPerPage, $scope.numPerPage, $scope.boardId);
    };

    $scope.$watch( 'currentPage', $scope.setPage );

    $scope.md2Html = function() {
        return $scope.html = $window.marked($scope.markdown);
    };

    $scope.initFromUrl = function(url) {
        return $http.get(url).success(function(data) {
            $scope.markdown = data;
            return $scope.md2Html();
        });
    };

    $scope.initFromText = function(text) {
        $scope.markdown = text;
        return $scope.md2Html();
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
}]);

angular.module('markdown', [])
    .config(['$routeProvider', function ($routeProvider) {
        var access = routingConfig.accessLevels;
        $routeProvider.when('/markdown/help', {
            templateUrl: '/app/markdown/help.tpl.html',
            controller: 'MarkdownHelpCtrl',
            access: access.user
        });
    }]);
angular.module('markdown').controller(['$scope', function($scope){

}]);
