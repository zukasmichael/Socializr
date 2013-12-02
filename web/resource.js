angular.module('resource', ['ngResource'])
  .factory('Groups', function ($resource) {
    var Groups = $resource('http://api.magroen.nl/group:id', {
      id:'@_id.$oid'
    });

    Groups.prototype.getFullName = function() {
      return this.name;
    };

    return Groups;
  })
  .controller('ResourceCtrl', function ($scope, Groups) {

    $scope.groups = Groups.query({}, function(groups){
      console.log($scope.groups.length);
    });
    
    $scope.remove = function (groups) {
      Groups['delete']({}, groups);
    };

    $scope.add = function () {
      var group = new Groups({
        name:'Superhero'
      });
      group.$save();
    };

    $scope.add = function () {
      var group = {
        name:'Superhero'
      };
      Groups.save(group);
    };

  });