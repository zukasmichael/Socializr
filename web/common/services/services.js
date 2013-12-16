'use strict';

angular.module('auth')
    .factory('Auth', function($http){

        var accessLevels = routingConfig.accessLevels
            , userRoles = routingConfig.userRoles
            , currentUser =  { user_name: '', role: userRoles.public };


        function changeUser(user) {
            angular.extend(currentUser, user);
        };

        return {
            authorize: function(accessLevel, role) {
                if(role === undefined)
                    role = currentUser.role;

                return accessLevel.bitMask & role.bitMask;
            },
            isLoggedIn: function(user) {
                if(user === undefined)
                    user = currentUser;
                return user.role.title == userRoles.user.title || user.role.title == userRoles.admin.title;
            },
            login: function(success, error) {
                $http.get('https://api.socializr.io/user/current').success(function (user) {
                    if(user.user_name !== ''){
                        user.role = userRoles.user;
                    }
                    changeUser(user);
                    success(user);
                }).error(error);
            },
            logout: function(success, error) {
                window.location = "https://api.socializr.io" + currentUser.logout_url;
            },
            accessLevels: accessLevels,
            userRoles: userRoles,
            user: currentUser
        };
    });