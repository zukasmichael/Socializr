'use strict';

angular.module('auth')
    .directive('accessLevel', ['Auth', function(Auth) {
        return {
            restrict: 'A',
            link: function($scope, element, attrs) {
                var prevDisp = element.css('display')
                    , userRole
                    , accessLevel;

                $scope.user = Auth.user;
                $scope.$watch('user', function(user) {
                    if(user.role)
                        userRole = user.role;
                    updateCSS();
                }, true);

                attrs.$observe('accessLevel', function(al) {
                    if(al) accessLevel = $scope.$eval(al);
                    updateCSS();
                });

                function updateCSS() {
                    if(userRole && accessLevel) {
                        if(!Auth.authorize(accessLevel, userRole))
                            element.css('display', 'none');
                        else
                            element.css('display', prevDisp);
                    }
                }
            }
        };
    }]);

angular.module('auth').directive('activeNav', ['$location', function($location) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var nestedA = element.find('a')[0];
            var path = nestedA.href;

            scope.location = $location;
            scope.$watch('location.absUrl()', function(newPath) {
                if (path === newPath) {
                    element.addClass('active');
                } else {
                    element.removeClass('active');
                }
            });
        }

    };

}]);
var md = function () {
    marked.setOptions({
        gfm:true,
        pedantic:false,
        sanitize:true
    });

    var toHtml = function (markdown) {
        if (markdown == undefined)
            return '';

        return marked(markdown);
    };

    return {
        toHtml:toHtml
    };
}();
angular.module('socializrApp').directive('markdown', function() {
    return {
        restrict: 'E',
        link: function(scope, element, attrs) {
            scope.$watch(attrs.ngModel, function(value, oldValue) {
                var markdown = value;
                var html = md.toHtml(markdown);
                element.html(html);
            });
        }
    };
});
angular.module('socializrApp').directive('whenScrolled', function() {
    return function(scope, elm, attr) {
        var raw = elm[0];

        elm.bind('scroll', function() {
            if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight) {
                scope.$apply(attr.whenScrolled);
            }
        });
    };
});